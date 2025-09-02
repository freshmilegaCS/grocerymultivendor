<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\UserModel;

class Notification extends BaseController
{
    public function notification()
    {
        if (!session()->has('email') || session()->get('is_email_verified') != 1) {
            return redirect()->to('/login');
        }
        
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
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

        return view('website/notification/notification', $data);
    }

   
}
