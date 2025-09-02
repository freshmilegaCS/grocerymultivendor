<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\BrandModel;
use App\Models\CartsModel;
use App\Models\UserModel;

class Brand extends BaseController
{
    public function index()
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

        $brandModel = new BrandModel();
        $data['brands'] = $brandModel->getBrandList();

        return view('website/brand/brand', $data);
    }
}
