<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\CategoryModel;
use App\Models\SubcategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantsModel;
use App\Models\UserModel;
use App\Models\ProductSortTypeModel;

class Subcategory extends BaseController
{
    public function subcategoryProductList($subcategorySlug)
    {
        $data['settings'] = $this->settings;

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

        $productSortTypeModel = new ProductSortTypeModel();
        $data['productSorts'] = $productSortTypeModel->findAll();

        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();

        $subcategory = $subcategoryModel->where('slug', $subcategorySlug)->first();
        $data['subcategory'] = $subcategory;

        if (isset($subcategory['category_id'])) {
            $category = $categoryModel->where('id', $subcategory['category_id'])->first() ?? [];
        } else {
            $category = [];
        }
        $data['category'] = $category;

        $data['subcategories'] = $category
            ? $subcategoryModel->where('category_id', $category['id'])->findAll()
            : [];


        $data['subcategorySlug'] = $subcategorySlug;

        return view('website/subcategory/subcategoryProductList', $data);
    }
}
