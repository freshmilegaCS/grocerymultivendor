<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\TaxModel;

class Tax extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('taxes')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('tax/tax', $data);
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
        if (!can_view('taxes')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $taxModel = new TaxModel();
        $taxes = $taxModel->where('is_delete', 0)->find();
        $output['data'] = [];
        $x = 1;

        foreach ($taxes as $row) {

            $action = "<a data-tooltip='tooltip' title='Edit Tax' href='" . base_url("admin/taxes/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Tax' onclick='deleteTax(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";
            $status = $row['is_active'] == 1 ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-danger'>InActive</span>";

            $output['data'][] = [
                $row['id'],
                $row['tax'],
                $row['percentage'],
                $status,
                $action,
            ];
            $x++;
        }

        return $this->response->setJSON($output);
    }
    public function add()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('taxes')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $tax = $this->request->getPost('tax');
        $percentage = $this->request->getPost('percentage');
        $taxModel = new TaxModel();

        $data = [
            'tax' => $tax,
            'percentage'  => $percentage
        ];

        if ($taxModel->insert($data)) {
            $output['success'] = true;
            $output['message'] = 'Tax added successfully ';
        } else {
            // Handle database insertion error
            $output['message'] = 'Something went wrong';
        }


        return $this->response->setJSON($output);
    }
    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('taxes')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $taxmodel = new TaxModel();
            $tax = $taxmodel->find($id);

            return view('tax/editTax', [
                'settings' => $settingModel->getSettings(), 
                'tax' => $tax
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
        if (!can_edit('taxes')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $tax = $this->request->getPost('tax');
        $taxid = $this->request->getPost('taxid');
        $percentage = $this->request->getPost('percentage');



        $data = [
            'tax' => $tax,
            'percentage' => $percentage,
        ];


        $taxModel = new TaxModel();


        if ($taxModel->where('id', $taxid)->set($data)->update()) {
            $output['success'] = true;
            $output['message'] = 'Tax updated successfully';
        } else {
            $output['message'] = 'Something went wrong';
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
        if (!can_delete('taxes')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $taxid = $this->request->getPost('taxid');

        $taxModel = new TaxModel();
        $data = [
            'is_delete' => 1
        ];
        if ($taxModel->set($data)->update($taxid)) {
            $output['success'] = true;
            $output['message'] = 'Tax deleted successfully';
        } else {
            $output['message'] = 'Something went wrong';
        }



        return $this->response->setJSON($output);
    }
}
