<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Libraries\CartSummery;
use App\Models\CartsModel;
use App\Models\UserModel;
use App\Models\PaymentMethodModel;
use App\Models\AddressModel;
use App\Models\DeliverableAreaModel;
use App\Libraries\GeoUtils;

class Checkout extends BaseController
{
    public function index()
    {
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }


        if ($this->settings['seller_only_one_seller_cart']) {
            return redirect()->to('/');
        }

        $cartSummery = new CartSummery();

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $user = null;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();

        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if($user == null){
            return redirect()->to('/login');
        }

        $data['user'] = $user;

        $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
        if ($cartItemCount > 0) {
            $data['cartItemCount'] = $cartItemCount;
        } else {
            return redirect()->to('/');
        }


        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];
        $data['wallet'] = $user['wallet'];

        $days = $this->generateDaysArray();
        $data['days'] = $days;

        // Calculate cart details
        list($subTotal, $taxTotal, $discountedPricesaving) = $cartSummery->calculateCartTotals($user['id']);
        $data['subtotal'] = $subTotal;
        $data['discountedPricesaving'] = $discountedPricesaving;
        $data['taxTotal'] = $taxTotal;

        // Calculate delivery charge details
        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $data['deliveryCharge'] = $deliveryDetails['deliveryCharge'];
        $data['minAmountForFreeDelivery'] = $deliveryDetails['minAmountForFreeDelivery'];
        $data['timeToTravel'] = $deliveryDetails['timeToTravel'];

        $paymentMethodModel = new PaymentMethodModel();
        $data['paymentMethods'] =  $paymentMethodModel->where('status', 1)->findAll();

        $data['schedule_delivery_status'] = json_decode($this->settings['schedule_delivery_status'], true);
        $data['takeaway_status'] = json_decode($this->settings['takeaway_status'], true);
        $data['home_delivery_status'] = json_decode($this->settings['home_delivery_status'], true);
        $data['seller_only_one_seller_cart'] = $this->settings['seller_only_one_seller_cart'];

        $data['additional_charge_status'] = $this->settings['additional_charge_status'];
        $data['additional_charge_name'] = $this->settings['additional_charge_name'];
        $data['additional_charge'] = $this->settings['additional_charge'];

        $data['minimum_order_amount'] = $this->settings['minimum_order_amount'];

        return view('website/checkout/checkout', $data);
    }

        private function generateDaysArray()
    {
        $days = [];

        // Loop through to create dates for the next 8 days
        for ($i = 0; $i < 9; $i++) {
            $date = new \DateTime();
            $date->modify("+$i day");

            $days[] = [
                'day' => $i === 0 ? 'Today' : $date->format('D'), // "Today" for the current day, else day name
                'date' => $date->format('M j'), // Month and day format
            ];
        }

        return $days;
    }




    public function oneSellerCartCheckout($seller_id)
    {
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }

        if (!$this->settings['seller_only_one_seller_cart']) {
            return redirect()->to('/');
        }

        $cartSummery = new CartSummery();

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        $data['user'] = $user;

        $cartItemCount = $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->countAllResults();
        if ($cartItemCount > 0) {
            $data['cartItemCount'] = $cartItemCount;
        } else {
            return redirect()->to('/');
        }


        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];
        $data['wallet'] = $user['wallet'];

        $days = $this->generateDaysArray();
        $data['days'] = $days;

        // Calculate cart details
        list($subTotal, $taxTotal, $discountedPricesaving) = $cartSummery->calculateCartTotals($user['id'], $seller_id);
        $data['subtotal'] = $subTotal;
        $data['discountedPricesaving'] = $discountedPricesaving;
        $data['taxTotal'] = $taxTotal;

        // Calculate delivery charge details
        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $data['deliveryCharge'] = $deliveryDetails['deliveryCharge'];
        $data['minAmountForFreeDelivery'] = $deliveryDetails['minAmountForFreeDelivery'];
        $data['timeToTravel'] = $deliveryDetails['timeToTravel'];

        $paymentMethodModel = new PaymentMethodModel();
        $data['paymentMethods'] =  $paymentMethodModel->where('status', 1)->findAll();

        $data['schedule_delivery_status'] = json_decode($this->settings['schedule_delivery_status'], true);
        $data['takeaway_status'] = json_decode($this->settings['takeaway_status'], true);
        $data['home_delivery_status'] = json_decode($this->settings['home_delivery_status'], true);
        $data['seller_only_one_seller_cart'] = $this->settings['seller_only_one_seller_cart'];

        $data['additional_charge_status'] = $this->settings['additional_charge_status'];
        $data['additional_charge_name'] = $this->settings['additional_charge_name'];
        $data['additional_charge'] = $this->settings['additional_charge'];

        $data['minimum_order_amount'] = $this->settings['minimum_order_amount'];

        $data['seller_id'] = $seller_id;

        return view('website/checkout/oneSellerCartCheckout', $data);
    }
}
