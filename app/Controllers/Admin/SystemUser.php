<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\AdminModel;
use App\Models\RoleModel;
use App\Models\SettingsModel;

class SystemUser extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('system-user')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $roleModel = new RoleModel();
            $role = $roleModel->findAll();

            return view('systemUser/systemUser', [
                'settings' => $settingModel->getSettings(),
                'roles' => $role
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function list()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('system-user')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $adminModel = new AdminModel();
        $subcategories = $adminModel->getUserlist();
        $output['data'] = [];

        foreach ($subcategories as $row) {

            $action = "<a data-tooltip='tooltip' title='Edit System User' href='" . base_url("admin/system-user/edit/{$row['id']}") . "' class='btn btn-primary-light  btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete System User' onclick='deleteSystemUser(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";

            $output['data'][] = [
                $row['id'],
                $row['fname'] . " " . $row['lname'],  // Category name from joined data
                $row['mobile'],           
                $row['username'],           
                $row['name'],           
                $action,
            ];
        }

        return $this->response->setJSON($output);
    }
    public function add()
    {
        $output = ['success' => false];

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('system-user')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Ensure the request is POST and has the required data
        if ($this->validate([
            'role_id' => 'required',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required',
            'pass' => 'required',
            'cpass' => 'required',
        ])) {
            $role_id = $this->request->getPost('role_id');
            $fname = $this->request->getPost('fname');
            $lname = $this->request->getPost('lname');
            $email = $this->request->getPost('email');
            $mobile = $this->request->getPost('mobile');
            $pass = $this->request->getPost('pass');


            // Load the database model for posts
            $adminModel = new AdminModel();
            // Check if the image data is valid

            $data = [
                'role_id' => $role_id,
                'fname' => htmlspecialchars($fname, ENT_QUOTES, 'UTF-8'),
                'lname' => htmlspecialchars($lname, ENT_QUOTES, 'UTF-8'),
                'username' => $email,
                'mobile' => $mobile,
                'password' =>  password_hash($pass, PASSWORD_DEFAULT)

            ];

            if ($adminModel->insert($data)) {
                $output['success'] = true;
                $output['message'] = 'System user added successfully';
            } else {
                // Handle database error
                $output['message'] = 'Failed to insert into database.';
            }
        } else {
            // Handle validation errors
            $output['message'] = 'Invalid input data.';
        }

        return $this->response->setJSON($output);
    }

    public function delete()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('system-user')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $user_id = $this->request->getPost('user_id');

        $adminModel = new AdminModel();

        if ($adminModel->delete($user_id)) {
            $output['success'] = true;
            $output['message'] = 'System user deleted successfully';
        } else {
            // Handle database insertion error
            $output['message'] = 'Database error occurred.';
        }



        return $this->response->setJSON($output);
    }

    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('system-user')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $adminModel = new AdminModel();
            $admin = $adminModel->find($id);
            $roleModel = new RoleModel();
            $role = $roleModel->findAll();

            return view('systemUser/editSystemUser', [
                'roles' => $role,
                'settings' => $settingModel->getSettings(),
                'admin' => $admin
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function update()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('system-user')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $user_id = $this->request->getPost('user_id');
        $role_id = $this->request->getPost('role_id');
        $fname = $this->request->getPost('fname');
        $lname = $this->request->getPost('lname');
        $email = $this->request->getPost('email');
        $mobile = $this->request->getPost('mobile');
        $pass = $this->request->getPost('pass');

        if ($pass == "") {
            $data = [
                'role_id' => $role_id,
                'fname' => htmlspecialchars($fname, ENT_QUOTES, 'UTF-8'),
                'lname' => htmlspecialchars($lname, ENT_QUOTES, 'UTF-8'),
                'username' => $email,
                'mobile' => $mobile,

            ];
        } else {
            $data = [
                'role_id' => $role_id,
                'fname' => htmlspecialchars($fname, ENT_QUOTES, 'UTF-8'),
                'lname' => htmlspecialchars($lname, ENT_QUOTES, 'UTF-8'),
                'username' => $email,
                'mobile' => $mobile,
                'password' =>  password_hash($pass, PASSWORD_DEFAULT)
            ];
        }




        $adminModel = new AdminModel();


        if ($adminModel->where('id', $user_id)->set($data)->update()) {
            $output['success'] = true;
            $output['message'] = 'Details updated successfully';
        } else {
            $output['message'] = 'Database error occurred.';
        }

        return $this->response->setJSON($output);
    }
}
