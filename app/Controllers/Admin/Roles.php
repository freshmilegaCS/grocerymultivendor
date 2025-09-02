<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\PermissionCategoryModel;
use App\Models\RoleModel;
use App\Models\RolePermissionModel;
use App\Models\SettingsModel;

class Roles extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('manage-roles') || !can_add('manage-roles')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            // Load the Role model
            $roleModel = new RoleModel();

            // Fetch active roles
            $roles = $roleModel->getActiveRoles();

            return view('/roles/roles', [
                'settings' => $settingModel->getSettings(),
                'roles' => $roles
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function add()
    {
        $output = ['success' => false];

        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('manage-roles')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Request data
        $role_name = $this->request->getPost('role_name');
        $timestamp = date('Y-m-d H:i:s');

        // Create slug
        $role_slug = str_replace(" ", "-", $role_name);
        $slug = preg_replace('/[^A-Za-z0-9-]/', '', strtolower($role_slug));

        // Insert role data
        $roleModel = new RoleModel();
        $roleData = [
            'name' => $role_name,
            'slug' => $slug,
            'is_active' => 1,
            'is_system' => 0,
            'is_superadmin' => 0,
            'created_at' => $timestamp,
        ];

        $inserted_role_id = $roleModel->insertRole($roleData);

        // Insert role permissions
        if ($inserted_role_id) {
            $permissionCategoryModel = new PermissionCategoryModel();
            $permissions = $permissionCategoryModel->getPermissions();
            $rolePermissionModel = new RolePermissionModel();

            foreach ($permissions as $permission) {
                $can_view = $permission['enable_view'] == 1 ? 1 : 0;
                $can_add = $permission['enable_add'] == 1 ? 1 : 0;
                $can_edit = $permission['enable_edit'] == 1 ? 1 : 0;
                $can_delete = $permission['enable_delete'] == 1 ? 1 : 0;

                $rolePermissionData = [
                    'role_id' => $inserted_role_id,
                    'perm_cat_id' => $permission['id'],
                    'can_view' => $can_view,
                    'can_add' => $can_add,
                    'can_edit' => $can_edit,
                    'can_delete' => $can_delete,
                    'created_at' => $timestamp,
                ];

                $rolePermissionModel->insertRolePermissions($rolePermissionData);
            }
            session()->setFlashdata('success', 'Role added successfully!');
            return redirect()->to('admin/roles');
        } else {
            session()->setFlashdata('error', 'Something went wrong');
            return redirect()->to('admin/roles');
        }



        return $this->response->setJSON($output);
    }
    public function delete()
    {
        $output = ['success' => false, 'message' => 'something went wrong'];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('manage-roles')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        // Check if banner ID is provided
        $roleId = $this->request->getPost('id');

        if ($roleId) {
            $roleModel = new RoleModel();

            // Attempt to delete the banner
            if ($roleModel->delete($roleId)) {
                $output['success'] = true;
                $output['message'] = 'Role deleted successfully';
            }
        }

        return $this->response->setJSON($output);
    }

}
