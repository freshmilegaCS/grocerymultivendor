<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Libraries\CartSummery;
use App\Models\CartsModel;
use App\Models\CouponModel;
use App\Models\UsedCouponModel;
use App\Models\UserModel;

class Coupon extends BaseController
{
    public function getCouponList()
    {
        $userModel = new UserModel();
        $couponModel = new CouponModel();
        $usedCouponModel = new UsedCouponModel();

        // Get current logged-in user (using session-based authentication)
        if (session()->get('login_type') == 'email') {
            $user = $userModel
                ->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        } elseif (session()->get('login_type') == 'mobile') {
            $user = $userModel
                ->where('mobile', session()->get('mobile'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        } else {
            $user = null;
        }

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found or inactive.'
            ]);
        }

        // Fetch used coupon IDs by the user
        $usedCoupons = $usedCouponModel->where('user_id', $user['id'])->findAll();
        $usedCouponIds = array_column($usedCoupons, 'coupon_id');

        // Set the timezone (you can adjust this as needed)
        date_default_timezone_set('Asia/Kolkata'); // Change to your preferred timezone

        // Base query: Fetch coupons that are not deleted, are active, and have a valid date
        $couponModel->where('is_delete', 0)
            ->where('status', 1)
            ->where('date >=', date("Y-m-d"));

        // Include coupons that belong to the current user or all users
        $couponModel->groupStart()
            ->where('user_id', $user['id']) // Coupons for the current user
            ->orWhere('user_id', 0) // Coupons for all users
            ->groupEnd();

        // Exclude coupons that have already been used by the user, unless they are multi-time usable
        if (!empty($usedCouponIds)) {
            $couponModel->groupStart()
                ->whereNotIn('id', $usedCouponIds) // Exclude used coupons
                ->orWhere('is_multitimes', 1) // Include multi-time usable coupons
                ->groupEnd();
        }

        $cartSummery = new CartSummery();
        list($subTotal, $taxTotal, $discountedPricesaving) = $cartSummery->calculateCartTotals($user['id']);
         


        // Execute the query
        $couponList = $couponModel->orderBy('id', 'DESC')->findAll();

        $output = [];
        foreach ($couponList as $coupon) {

            $value = 0;
            if ($coupon['coupon_type'] == 1) {
                $value = $subTotal * (int)$coupon['value'] /100;
            } else if ($coupon['coupon_type'] == 2) {
                $value = $coupon['value'];
            }

            $output[] = [
                "coupon_id" => $coupon['id'],
                "title" => $coupon['coupon_title'],
                "code" => $coupon['coupon_code'],
                "coupon_type" => $coupon['coupon_type'],
                "coupon_value" => $coupon['value'],
                "image" => base_url() . $coupon['coupon_img'],
                "value" => $value,
                "min_order_amount" => $coupon['min_order_amount'],
                "description" => $coupon['description'],
                "is_multitimes" => (int) $coupon['is_multitimes'],
                "user_id" => $coupon['user_id'],
                "validTill" => $coupon['date'], // Added validity date
            ];
        }

        if (!empty($output)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Valid coupon list found',
                'data' => $output
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No coupons available at the moment.'
        ]);
    }

    public function applyCoupon()
    {
        $cartSummery = new CartSummery();

        $data = $this->request->getJSON(true);

        $userModel = new UserModel();
        $couponModel = new CouponModel();
        $usedCouponModel = new UsedCouponModel();

        // Get logged-in user
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'User not found or inactive.'
            ]);
        }

        // Calculate subtotal, tax, and delivery charges
        list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id']);

        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];


        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = $additional_charge_status == 1 ? (float)$this->settings['additional_charge'] : 0;

        // Fetch the coupon
        $coupon = $couponModel
            ->where('id', $data['coupon_id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$coupon) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Invalid or inactive coupon.'
            ]);
        }

        // Fetch used coupon entry
        $usedCoupon = $usedCouponModel
            ->where('coupon_id', $coupon['id'])
            ->where('user_id', $user['id'])
            ->first();

        // Validate coupon conditions
        $today = date('Y-m-d');
        $total = $subTotal + $taxTotal + $deliveryCharge + $additional_charge;

        $walletBalance = (float) $user['wallet'];
        list($walletApplied,) = $this->calculateWalletAmount($data, $walletBalance, $total);

        $total = $subTotal + $taxTotal + $deliveryCharge + $additional_charge - $walletApplied;

        // Check coupon eligibility
        if ($coupon['is_multitimes'] == 1 && $today <= $coupon['date'] && (!$usedCoupon || $usedCoupon['user_id'] == $user['id'])) {
            if ($subTotal >= $coupon['min_order_amount']) {
                if ($total < $coupon['value']) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'Unable to apply coupon. Grand total is smaller than coupon value.'
                    ]);
                }

                return $this->response->setStatusCode(200)->setJSON([
                    'status' => 'success',
                    'message' => 'Coupon applied successfully.',
                    'data' => [
                        'coupon_id' => $coupon['id'],
                        'coupon_code' => $coupon['coupon_code'],
                        'coupon_amount' => $coupon['value'],
                        'min_order_amount' => $coupon['min_order_amount'],
                        'coupon_type' => $coupon['coupon_type'],
                    ]
                ]);
            } else {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'Minimum order amount not met to apply this coupon.'
                ]);
            }
        } elseif ($coupon['is_multitimes'] == 0 && $usedCoupon) {
            return $this->response->setStatusCode(409)->setJSON([
                'status' => 'error',
                'message' => 'Coupon already used.'
            ]);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Coupon not valid or expired.'
            ]);
        }
    }

    private function calculateWalletAmount($data, $walletBalance, $total)
    {
        // Initialize default response values
        $walletApplied = 0;
        $remainingWalletBalance = $walletBalance;

        // Check if wallet data is provided and contains a valid wallet_applied key
        if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
            $requestedWalletApplied = (float)$data['wallet']['wallet_applied'];

            // Ensure the requested wallet amount doesn't exceed the user's wallet balance
            if ($requestedWalletApplied > $walletBalance) {
                // Invalid case: requested more than available balance
                $walletApplied = $walletBalance;
                $remainingWalletBalance = 0;
            } else {
                // Requested wallet amount is valid
                $walletApplied = $requestedWalletApplied;

                // Check if the requested amount exceeds the total
                if ($walletApplied > $total) {
                    $walletApplied = $total; // Use only the amount needed to cover the total
                    $remainingWalletBalance = $walletBalance - $walletApplied;
                    $total = 0;
                } else {
                    // Reduce the total and adjust wallet balance accordingly
                    $total -= $walletApplied;
                    $remainingWalletBalance = $walletBalance - $walletApplied;
                }
            }
        } elseif (!isset($data['wallet'])) {
            // If wallet data is not sent, do not apply wallet balance
            $walletApplied = 0;
            $remainingWalletBalance = $walletBalance;
        }

        return [$walletApplied, $remainingWalletBalance];
    }


    public function removeCoupon()
    {

        $userModel = new UserModel();

        // Get logged-in user
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found or inactive.'
            ]);
        }



        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Coupon removed successfully.',

        ]);
    }
}
