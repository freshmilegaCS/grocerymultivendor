<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\UserModel;
use App\Models\WalletModel;

use App\Libraries\CartSummery;


class Wallet extends BaseController
{
    public function wallet()
    {
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

        $walletModel = new WalletModel();
        $data['wallets'] = $walletModel->where('user_id', $user['id'])->findAll();

        $data['currentWalletAmount'] = $user['wallet'];
        $data['totalCredit'] = $walletModel
            ->selectSum('amount')
            ->where('user_id', $user['id'])
            ->groupStart()
                ->where('flag', 'fund_return')
                ->orWhere('flag', 'top_up')
            ->groupEnd()
            ->get()
            ->getRow()
            ->amount;

        // Sum of amount where flag is 'debit'
        $data['totalDebit'] = $walletModel
            ->selectSum('amount')
            ->where('user_id', $user['id'])
            ->where('flag', 'debit')
            ->get()
            ->getRow()
            ->amount;

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];

        return view('website/wallet/wallet', $data);
    }


    public function applyWallet()
    {
        $cartSummery = new CartSummery();

        $data = $this->request->getJSON(true);
        $userModel = new UserModel();

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

        list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id']);


        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        list($coupon_amount,) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);


        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $walletBalance = (float) $user['wallet'];
        $total = $subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount;

        // Initialize default response values
        $walletApplied = 0;
        $remainingWalletBalance = $walletBalance;

        // If wallet data is sent from the browser
        if (!empty($wallet)) {
            $requestedWalletApplied = (float) $wallet['wallet_applied'];

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
        } else {
            // If no wallet data is sent, use default wallet balance logic
            if ($walletBalance > 0) {
                if ($walletBalance >= $total) {
                    $walletApplied = $total;
                    $remainingWalletBalance = $walletBalance - $total;
                    $total = 0;
                } else {
                    $walletApplied = $walletBalance;
                    $remainingWalletBalance = 0;
                    $total -= $walletBalance;
                }
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Wallet applied successfully.',
            'data' => [
                'wallet_applied' => round((float)$walletApplied, 2),
                'remaining_wallet_balance' => round((float)$remainingWalletBalance, 2)
            ]
        ]);
    }

    public function removeWallet()
    {

        $userModel = new UserModel();

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
        $walletBalance = (float) $user['wallet'];


        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Wallet applied successfully.',
            'data' => [
                'remaining_wallet_balance' => $walletBalance,
            ]
        ]);
    }
}
