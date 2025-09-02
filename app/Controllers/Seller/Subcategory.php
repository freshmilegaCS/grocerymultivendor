<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\SubcategoryModel;

class Subcategory extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {
            $settingModel = new SettingsModel();
            $appSetting = $settingModel->getSettings();

            return view('sellerPanel/subcategory', [
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

        $subcategoryModel = new SubcategoryModel();
        $subcategories = $subcategoryModel->getSubcategoriesWithDetailsForSeller();
        $output = [];
        $x = 1;

        foreach ($subcategories as $row) {
            $img = "<a href='" . base_url($row['img']) . "' target='_blank'>
                        <img class='media-object round-media' src='" . base_url($row['img']) . "' alt='image' style='height: 70px; width: 40%'>
                    </a>";
         
            $output['data'][] = [
                $row['id'],
                $row['category_name'],  // Category name from joined data
                $row['name'],           // Subcategory name
                $img,
                $row['product_count'], 
            ];
            $x++;
        }

        return $this->response->setJSON($output);
    }
}
