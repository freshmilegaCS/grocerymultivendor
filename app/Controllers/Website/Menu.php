<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\CartsModel;
use App\Models\UserModel;


class Menu extends BaseController
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
        }else{
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount; $data['user'] = $user;
        }

        $data['email'] = session()->get('email');
        $data['name'] = session()->get('name');

        return view('website/menu/menu', $data);
    }
}