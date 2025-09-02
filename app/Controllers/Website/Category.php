<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\CategoryModel;
use App\Models\SubcategoryModel;
use App\Models\UserModel;

class Category extends BaseController
{
    public function index()
    {
        
        $data['settings'] = $this->settings;

        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();


        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $user = null;

        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        // Redirect to loader if no city is selected
        if (session()->get('city_id') == null) {
            $data['session_load'] = 0;
            return view('website/loader', $data);
        }

        $data['categories'] = $categoryModel->orderBy('row_order', 'ASC')->findAll();
        foreach ($data['categories'] as &$category) {
            // Fetch the first subcategory for each category ID
            $subcategory = $subcategoryModel->where('category_id', $category['id'])
                ->orderBy('row_order', 'ASC')
                ->first();
            $category['firstSubcategory'] = $subcategory;
        }

        return view('website/category/category', $data);
    }

    
}
