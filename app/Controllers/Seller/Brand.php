<?php

namespace App\Controllers\Seller; 
use App\Controllers\BaseController; 

use App\Models\BrandModel;
use App\Models\SettingsModel;

class Brand extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {
            $settingModel = new SettingsModel();
            $appSetting = $settingModel->getSettings();


            return view('sellerPanel/brand', [
                'settings' => $appSetting,
            ]);
        } else {
            return redirect()->to('seller/auth/login'); 
        }
    }
    public function list()
    {
        if (! session()->has('user_id') && session('account_type') == 'Seller') {
            return redirect()->to('seller/login'); // Redirect to login if session is not set
        }

        $brandModel = new BrandModel();

        $categories = $brandModel->orderBy('row_order')->findAll();
        $output = [];
        $x = 1;

        foreach ($categories as $row) {
            $img = "<a href='" . base_url($row['image'])."' target='_blank'>
                        <img class='media-object round-media' src='" . base_url($row['image'])."' alt='image' style='height: 75px; width: 40%'>
                    </a>";

            $output['data'][] = [
                $row['id'],
                $row['brand'],
                $img,
            ];
            $x++;
        }

        return $this->response->setJSON($output);
    }
}
