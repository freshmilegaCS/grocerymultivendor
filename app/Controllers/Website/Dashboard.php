<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\CartsModel;
use App\Models\OrderModel;
use App\Models\UserModel;


class Dashboard extends BaseController
{
    public function index()
    {
        date_default_timezone_set($this->timeZone['timezone']);
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
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
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];
        $data['user_wallet'] = $user['wallet'];

        $data['email'] = session()->get('email');
        $data['name'] = session()->get('name');

        $orderModel = new OrderModel();
        $data['orderCount'] = $orderModel->where('user_id', $user['id'])->countAllResults();

        $currentHour = (int) date('H');

        // Determine the greeting based on the time
        if ($currentHour >= 5 && $currentHour < 12) {
            $data['greeting'] = "Good Morning";
        } elseif ($currentHour >= 12 && $currentHour < 17) {
            $data['greeting'] = "Good Afternoon";
        } elseif ($currentHour >= 17 && $currentHour < 21) {
            $data['greeting'] = "Good Evening";
        } else {
            $data['greeting'] = "Good Night";
        }


        return view('website/dashboard/dashboard', $data);
    }
}
