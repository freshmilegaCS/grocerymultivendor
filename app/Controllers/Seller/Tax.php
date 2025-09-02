<?php

namespace App\Controllers\Seller; 
use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\TaxModel;

class Tax extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {
            $settingModel = new SettingsModel();
            $appSetting = $settingModel->getSettings();

            return view('sellerPanel/tax', [
                'settings' => $appSetting
            ]);
        } else {
            return redirect()->to('seller/auth/login');
        }
    }
    public function list()
    {
        // Ensure session is started
        if (! session()->has('user_id') && session('account_type') == 'Seller') {
            return redirect()->to('seller/login'); // Redirect to login if session is not set
        }

        $taxModel = new TaxModel();
        $taxes = $taxModel->find();
        $output['data'] = [];
        $x = 1;

        foreach ($taxes as $row) {

            $action = "<a data-tooltip='tooltip' title='Edit Tax' href='" . base_url("taxes/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Tax' onclick='deleteTax(" . $row['id'] . ")' class='btn btn-danger btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";
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

}
