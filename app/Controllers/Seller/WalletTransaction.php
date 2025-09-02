<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use App\Models\CountryModel;
use App\Models\OrderProductModel;
use App\Models\SellerModel;
use App\Models\SellerWalletTransactionModel;
use App\Models\SettingsModel;

class WalletTransaction extends BaseController
{

    public function transactionListView()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {

            $settingModel = new SettingsModel();
            $sellerModel = new SellerModel();
            $seller = $sellerModel->select('id, name, store_name, mobile, balance')->where('is_delete', 0)->where('id', session()->get('user_id'))->first();
            $countryModel = new CountryModel();
            $country = $countryModel->where('is_active', 1)->first();
            return view('sellerPanel/transactionList', [
                'settings' => $settingModel->getSettings(), 
                'seller' => $seller,
                'country' => $country
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function transactionList()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }



        $sellerWalletTransactionModel = new SellerWalletTransactionModel();
        // Get input filters
        $txn_date = $this->request->getPost('txn_date');
        $method = $this->request->getPost('method');
        if (empty($txn_date)) {
            $today = date('Y-m-d');
            $txn_date = "$today - $today";
        }
        $dates = explode(' - ', $txn_date);

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        $builder = $sellerWalletTransactionModel->select(
            'seller_wallet_transaction.id, seller_wallet_transaction.seller_id, seller_wallet_transaction.order_id, seller_wallet_transaction.order_products_id, seller_wallet_transaction.type, seller_wallet_transaction.amount, seller_wallet_transaction.message, seller_wallet_transaction.created_at,seller_wallet_transaction.updated_at, seller.name, seller.store_name, seller.mobile, seller.id as seller_id'
        )
            ->join('seller', 'seller.id = seller_wallet_transaction.seller_id', 'left');

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(seller_wallet_transaction.created_at) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(seller_wallet_transaction.created_at) <=', date('Y-m-d', strtotime($dates[1])));
        }
        $builder->where('seller_wallet_transaction.seller_id', session()->get('user_id'));

        if (!empty($method)) {
            $builder->where('seller_wallet_transaction.type', $method);
        }
        $builder->where('seller_wallet_transaction.status', 2);

        // Fetch data
        $query = $builder->get();
        $fundtransferLists = $query->getResultArray();
        $output['data'] = [];
        foreach ($fundtransferLists as  $fundtransferList) {
            if ($fundtransferList['type'] == 'credit') {
                $type = '<span class="badge badge-success">Credit</span>';
            } else {
                $type = '<span class="badge badge-danger">Debit</span>';
            }

            if (isset($fundtransferList['order_products_id']) && $fundtransferList['order_products_id'] != 0) {
                $orderProductModel = new  OrderProductModel();
                $order_item_details = $orderProductModel->where('id', $fundtransferList['order_products_id'])->first();
                $product_name = $order_item_details['product_name'];
                $product_variant_name = $order_item_details['product_variant_name'];
            } else {
                $product_name = '';
                $product_variant_name = '';
            }
            $output['data'][] = [
                $fundtransferList['id'],
                $fundtransferList['name'] . "<br>" . $fundtransferList['store_name'],
                $fundtransferList['order_id'],
                $fundtransferList['order_products_id'],
                $product_name,
                $product_variant_name,
                $type,
                $country['currency_symbol'] . " " . round($fundtransferList['amount'], 2),
                $fundtransferList['message'],
                $fundtransferList['created_at'],

            ];
        }
        return $this->response->setJSON($output);
    }
    public function withdrawalRequestView()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {

            $settingModel = new SettingsModel();
            $sellerModel = new SellerModel();
            $seller = $sellerModel->select('id, name, store_name, mobile, balance')->where('is_delete', 0)->where('id', session()->get('user_id'))->first();
            $countryModel = new CountryModel();
            $country = $countryModel->where('is_active', 1)->first();
            return view('sellerPanel/withdrawalRequest', [
                'settings' => $settingModel->getSettings(), 
                'seller' => $seller,
                'country' => $country
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function withdrawalRequestList()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }



        $sellerWalletTransactionModel = new SellerWalletTransactionModel();
        // Get input filters
        $txn_date = $this->request->getPost('txn_date');
        if (empty($txn_date)) {
            $today = date('Y-m-d');
            $txn_date = "$today - $today";
        }
        $dates = explode(' - ', $txn_date);

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        $builder = $sellerWalletTransactionModel->select(
            'id, seller_id,  amount, message, remark, created_at,updated_at, status'
        );

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(created_at) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(created_at) <=', date('Y-m-d', strtotime($dates[1])));
        }
        $builder->where('seller_id', session()->get('user_id'));
        $builder->where('this_is_request', 1);


        // Fetch data
        $query = $builder->get();
        $fundtransferLists = $query->getResultArray();
        $output['data'] = [];
        foreach ($fundtransferLists as  $fundtransferList) {
            if ($fundtransferList['status'] == 1) {
                $status = '<span class="badge badge-warning">Pending</span>';
            } elseif ($fundtransferList['status'] == 2) {
                $status = '<span class="badge badge-success">Payment Done</span>';
            } else {
                $status = '<span class="badge badge-danger">Rejected</span>';
            }

            $output['data'][] = [
                $fundtransferList['id'],
                $country['currency_symbol'] . " " . round($fundtransferList['amount'], 2),
                $fundtransferList['message'],
                $status,
                $fundtransferList['remark'],
                $fundtransferList['created_at'],
                $fundtransferList['updated_at'],
            ];
        }
        return $this->response->setJSON($output);
    }
    public function addTransaction()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }



        $sellerWalletTransactionModel = new SellerWalletTransactionModel();
        // Get input filters
        $seller_id = session()->get('user_id');
        $amount = $this->request->getPost('amount');
        $remark = $this->request->getPost('remark');

        $sellerModel = new SellerModel();
        $seller = $sellerModel->where('is_delete', 0)->where('id', $seller_id)->first();
        $success = false;
        if ($seller['balance'] >= $amount) {
            $data = [
                'seller_id' => $seller_id,
                'type' => 'debit',
                'amount' => $amount,
                'message' => $remark,
                'status' => 1,
                'this_is_request' => 1,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $success = $sellerWalletTransactionModel->insert($data);
        } else {
            $output = ['success' => false, "message" => "Amount should be equal or greater than balance."];
            return $this->response->setJSON($output);
        }

        if ($success) {
            $output = ['success' => true, "message" => "Fund request done successfully"];
        } else {
            $output = ['success' => false, "message" => "Something went wrong"];
        }


        return $this->response->setJSON($output);
    }
}
