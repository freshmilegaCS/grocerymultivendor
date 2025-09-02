<?php

namespace App\Controllers\Seller; 
use App\Controllers\BaseController; 

use App\Models\CategoryModel;
use App\Models\SettingsModel;

class Category extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {
            $settingModel = new SettingsModel();
            $appSetting = $settingModel->getSettings();


            return view('sellerPanel/category', [
                'settings' => $appSetting,
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

        $categoryModel = new CategoryModel();

        $categories = $categoryModel->getCategoriesForSellerWithSubCount();
        $output = [];
        $x = 1;

        foreach ($categories as $row) {
            $img = "<a href='" . base_url($row['category_img'])."' target='_blank'>
                        <img class='media-object round-media' src='" . base_url($row['category_img'])."' alt='image' style='height: 75px; width: 40%'>
                    </a>";

            $output['data'][] = [
                $row['id'],
                $row['category_name'],
                $img,
                $row['sub_count']
            ];
            $x++;
        }

        return $this->response->setJSON($output);
    }
}
