<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\PermissionCategoryModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\SettingsModel;

class RolePermission extends BaseController
{
    public function index($roleId)
    {
        $session = session();
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            if (!can_view('manage-roles')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $permissionCategoryModel = new PermissionCategoryModel();
            $rolePermissionModel = new RolePermissionModel();
            $roleModel = new RoleModel();

            // Fetch all permission categories 
            $data['categories'] = $permissionCategoryModel->getAllCategories();

            // Fetch role permissions for this role
            $data['role_permissions'] = $rolePermissionModel->getPermissionsByRole($roleId);
            $data['role_info'] = $roleModel->where('id', $roleId)->first();
            // Map role permissions to category ids for easier use in view
            $data['role_permissions_map'] = [];
            foreach ($data['role_permissions'] as $permission) {
                $data['role_permissions_map'][$permission['perm_cat_id']] = $permission;
            }

            return view('/roles/assignPermission', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function update()
    {
        $output = ['success' => false, 'message' => 'Unable to update'];
        $session = session();

        // Ensure only admin role can perform the operation
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            if (!can_edit('manage-roles')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            if ($this->settings['demo_mode']) {
                $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $permissionEncodedJson = $this->request->getPost('permissionEncodedJson');

            if ($permissionEncodedJson) {
                $rolesPermissionsModel = new RolePermissionModel();
                $json = json_decode($permissionEncodedJson);

                foreach ($json as $mydata1) {
                    // Ensure valid data
                    $can_view = $mydata1->can_view ? 1 : 0;
                    $can_add = $mydata1->can_add ? 1 : 0;
                    $can_edit = $mydata1->can_edit ? 1 : 0;
                    $can_delete = $mydata1->can_delete ? 1 : 0;
                    $select_permission_id = (int)$mydata1->select_permission_id;
                    $category_id = (int)$mydata1->category_id;
                    $role_id = (int)$mydata1->role_id;


                    // Perform update
                    
                    if ($select_permission_id == 0) {
                        $data = [
                            'can_view' => $can_view,
                            'can_add' => $can_add,
                            'can_edit' => $can_edit,
                            'can_delete' => $can_delete,
                            'perm_cat_id' =>$category_id,
                            'role_id' => $role_id
                        ];
                        $rolesPermissionsModel->insert($data);
                    } else {
                        $data = [
                            'can_view' => $can_view,
                            'can_add' => $can_add,
                            'can_edit' => $can_edit,
                            'can_delete' => $can_delete
                        ];
                        $rolesPermissionsModel->update($select_permission_id, $data);
                    }
                }

                // Update response if the update was successful
                $output = ['success' => true, 'message' => 'Permission updated successfully'];
            }
        }

        // Send response
        return $this->response->setJSON($output);
    }
}
