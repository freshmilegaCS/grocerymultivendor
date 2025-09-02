<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DeliveryBoyModel;
use App\Models\DeviceTokenModel;
use App\Models\OrderReturnRequestModel;
use App\Models\SellerModel;
use App\Models\SellerWalletTransactionModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Models\WalletModel;

class ReturnRequest extends BaseController
{
    public function returnRequest()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $sellerModel = new SellerModel();
            $data['sellers'] = $sellerModel->select('id, name, store_name, mobile, balance')->where('is_delete', 0)->findAll();
            return view('orders/returnRequest', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function returnRequestlist()
    {
        if (!session()->has('user_id') || session('account_type') !== 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        $orderReturnRequestModel = new OrderReturnRequestModel();
        $orderDate = $this->request->getPost('report_date');
        $seller = $this->request->getPost('seller');
        $status = $this->request->getPost('status');


        // Handle default order date
        if (empty($orderDate)) {
            $today = date('Y-m-d');
            $orderDate = "$today - $today";
        }
        $dates = explode(' - ', $orderDate);

        // Base query
        $builder = $orderReturnRequestModel->select(
            'order_products.product_name, 
            order_products.product_variant_name AS variant_name, 
            order_products.id AS order_products_id,  
            order_return_request.id AS order_return_request_id, 
            order_return_request.reason, 
            order_return_request.status, 
            order_return_request.remark, 
            order_return_request.created_at, 
            order_return_request.updated_at, 
            order_products.price,
            order_products.discounted_price,
            order_products.tax_amount, 
            order_products.quantity, 
            (
                (CASE 
                    WHEN order_products.discounted_price = 0 
                    THEN order_products.price 
                    ELSE order_products.discounted_price 
                END) * order_products.quantity
            ) + order_products.tax_amount AS total_amount, 
            user.name as user_name, 
            user.mobile'
        );
        $builder->join('order_products', 'order_return_request.order_products_id = order_products.id', 'left');
        $builder->join('user', 'order_products.user_id = user.id', 'left');
        if (!empty($seller)) {
            $builder->where('order_products.seller_id', $seller);
        }
        if (!empty($status)) {
            $builder->where('order_return_request.status', $status);
        }
        if (!empty($dates) && is_array($dates)) {
            $builder->where('DATE( order_return_request.created_at) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE( order_return_request.created_at) <=', date('Y-m-d', strtotime($dates[1])));
        }



        // Fetch data
        $query = $builder->get();
        $reports = $query->getResultArray();

        // Prepare output
        $output['data'] = [];
        foreach ($reports as $index => $order) {

            if ($order['status'] == 1) {
                $status =  "<span class='badge badge-secondary'>Pending</span>";
                $action = "<a data-tooltip='tooltip' title='Edit Request' onclick='updateRequest({$order['order_return_request_id']})' class='btn btn-primary-light  btn-xs'>
            <i class='fi fi-tr-customize-edit'></i></a>";
            } else if ($order['status'] == 2) {
                $status =  "<span class='badge badge-info'>Approved</span>";
                $action = "<a data-tooltip='tooltip' title='Edit Request' onclick='updateRequest({$order['order_return_request_id']})' class='btn btn-primary-light  btn-xs'>
            <i class='fi fi-tr-customize-edit'></i></a>";
            } else if ($order['status'] == 3) {
                $status =  "<span class='badge badge-danger'>Rejected</span>";
                $action = "";
            } else if ($order['status'] == 4) {
                $status =  "<span class='badge badge-warning'>Returned To DeliveryBoy</span>";
                $action = "<a data-tooltip='tooltip' title='Edit Request' onclick='updateToReturned({$order['order_return_request_id']})' class='btn btn-primary-light  btn-xs'>
                <i class='fi fi-tr-customize-edit'></i></a>";
            } else if ($order['status'] == 5) {
                $status =  "<span class='badge badge-success'>Returned Successfully</span>";
                $action = "";
            }

            $output['data'][] = [
                $order['order_products_id'],
                $order['user_name'],
                $order['product_name'],
                $order['variant_name'],
                $order['price'],
                $order['discounted_price'],
                $order['quantity'],
                $order['total_amount'],
                $status,
                $order['created_at'],
                $action
            ];
        }

        return $this->response->setJSON($output);
    }

    public function viewReturnRequest()
    {
        if (!session()->has('user_id') || session('account_type') !== 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();
        $returnRequest = $this->request->getPost('return_request');

        // Handle default order date
        if (empty($returnRequest)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
        }

        $query = $orderReturnRequestModel->select(
            'order_return_request.id,order_return_request.delivery_boy_id, order_return_request.status, order_return_request.remark, order_return_request.reason, order_return_request.order_id, address.city_id'
        )
            ->join('order_products', 'order_return_request.order_products_id = order_products.id', 'left')
            ->join('orders', 'order_return_request.order_id = orders.id', 'left')
            ->join('address', 'orders.address_id = address.id', 'left')
            ->join('user', 'order_products.user_id = user.id', 'left')
            ->where('order_return_request.id', $returnRequest)
            ->first();

        if ($query) {
            $deliveryBoyModel = new DeliveryBoyModel();
            $delivery_boy = $deliveryBoyModel->select('id, name, mobile')->where('is_delete', 0)->where('status', 1)->where('is_available', 1)->where('a_status', 1)->where('city_id', $query['city_id'])->findAll();
            return $this->response->setJSON(['success' => true, 'response' => $query, 'delivery_boy' => $delivery_boy]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
        }
    }
    public function updateReturnRequest()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        helper('firebase_helper');

        $request_id = $this->request->getPost('request_id');
        $status = $this->request->getPost('update_status');
        $delivery_boy_id = $this->request->getPost('delivery_boy_id');
        $remark = $this->request->getPost('remark');

        $validationRules = [
            'update_status' => 'required',
            'request_id' => 'required',
        ];
        if ($status == 2) {
            $validationRules['delivery_boy_id'] = 'required';
        }


        if (!$this->validate($validationRules)) {
            $output = ['success' => false, "message" => $this->validator->getErrors()];
            return $this->response->setJSON($output);
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();
        if ($status == 2) {
            $data = [
                'status' => $status,
                'remark' => $remark,
                'updated_at' => date("Y-m-d H:i:s"),
                'delivery_boy_id' => $delivery_boy_id
            ];
        } else {
            $data = [
                'status' => $status,
                'remark' => $remark,
                'updated_at' => date("Y-m-d H:i:s")
            ];
        }
        $success = $orderReturnRequestModel->where('id', $request_id)->set($data)->update();

        if ($success) {
            $userDetails = $orderReturnRequestModel->select('orders.user_id, orders.id')->join('orders', 'orders.id = order_return_request.order_id', 'left')->where('order_return_request.id', $request_id)->first();

            $deviceTokenModel = new DeviceTokenModel();
            $userToken = $deviceTokenModel->where('user_type', 2)->where('user_id', $userDetails['user_id'])->orderBy('id', 'desc')->first();
            $dataForNotification = [
                'screen' => 'Notification',
            ];
            if (isset($userToken['app_key'])) {
                sendFirebaseNotification($userToken['app_key'], 'Your item return status updated', 'For order id ' . $userDetails['id'], $dataForNotification);
            }
            if ($status == 2) {
                $deliveryUserToken = $deviceTokenModel->where('user_type', 3)->where('user_id', $delivery_boy_id)->orderBy('id', 'desc')->first();
                if (isset($deliveryUserToken['app_key'])) {
                    sendFirebaseNotification($deliveryUserToken['app_key'], 'New Return Item Assigned For You', 'For order id ' . $userDetails['id'], $dataForNotification);
                }
            }
            $output = ['success' => true, "message" => "Status updated successfully"];
        } else {
            $output = ['success' => false, "message" => "Something went wrong"];
        }

        return $this->response->setJSON($output);
    }

    public function viewReturnedToStoreRequest()
    {
        if (!session()->has('user_id') || session('account_type') !== 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();
        $returnRequest = $this->request->getPost('return_request');

        // Handle default order date
        if (empty($returnRequest)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
        }

        $query = $orderReturnRequestModel->select(
            'order_return_request.id, order_return_request.status, order_return_request.remark, order_return_request.reason, order_return_request.order_id, address.city_id'
        )
            ->join('order_products', 'order_return_request.order_products_id = order_products.id', 'left')
            ->join('orders', 'order_return_request.order_id = orders.id', 'left')
            ->join('address', 'orders.address_id = address.id', 'left')
            ->join('user', 'order_products.user_id = user.id', 'left')
            ->where('order_return_request.id', $returnRequest)
            ->first();

        if ($query) {
            $deliveryBoyModel = new DeliveryBoyModel();
            $delivery_boy = $deliveryBoyModel->select('id, name, mobile')->where('is_delete', 0)->where('status', 1)->where('is_available', 1)->where('a_status', 1)->where('city_id', $query['city_id'])->findAll();
            return $this->response->setJSON(['success' => true, 'response' => $query, 'delivery_boy' => $delivery_boy]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
        }
    }

    public function updateReturnedToStoreRequest()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        $return_request_id = $this->request->getPost('return_request_id');
        $update_returned_status = $this->request->getPost('update_returned_status');

        $validationRules = [
            'update_returned_status' => 'required',
            'return_request_id' => 'required',
        ];

        if (!$this->validate($validationRules)) {
            $output = ['success' => false, "message" => $this->validator->getErrors()];
            return $this->response->setJSON($output);
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();

        $data = [
            'status' => $update_returned_status,
            'updated_at' => date("Y-m-d H:i:s")
        ];

        $success = $orderReturnRequestModel->where('id', $return_request_id)->set($data)->update();

        if ($success) {
            $sellerWalletTransactionModel =  new SellerWalletTransactionModel();
            $requestDetails = $orderReturnRequestModel->select('order_return_request.order_id, order_return_request.order_products_id, order_products.user_id, (
                (CASE 
                    WHEN order_products.discounted_price = 0 
                    THEN order_products.price 
                    ELSE order_products.discounted_price 
                END) * order_products.quantity
            ) + order_products.tax_amount AS total_amount')
                ->join('order_products', 'order_products.id = order_return_request.order_products_id', 'left')
                ->where('order_return_request.id', $return_request_id)
                ->first();
            $findRefundAmount = $sellerWalletTransactionModel->select('id, amount, seller_id')->where('order_id', $requestDetails['order_id'])->where('order_products_id', $requestDetails['order_products_id'])->where('type', 'credit')->orderBy('id', 'desc')->first();

            $transactionData = [
                'order_id' => $requestDetails['order_id'],
                'order_products_id' => $requestDetails['order_products_id'],
                'seller_id' => $findRefundAmount['seller_id'],
                'type' => 'debit',
                'remark' => 'Amount return of order item return',
                'amount' => $findRefundAmount['amount'],
                'order_id' => $requestDetails['order_id'],
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
            $sellerWalletTransactionModel->insert($transactionData);

            $sellerModel =  new SellerModel();
            $findSellerBalanceDetails = $sellerModel->select('balance')->where('id', $findRefundAmount['seller_id'])->first();
            $sellerModel->where('id', $findRefundAmount['seller_id'])->set(['balance' => $findSellerBalanceDetails['balance'] - $findRefundAmount['amount']])->update();
            $walletModel = new WalletModel();
            $actualWalletAmount = $walletModel->calculateActualWalletAmount($requestDetails['user_id'], $findRefundAmount['amount'], 'credit');

            $remarkForUserWallet = 'Amount return of order item return for order ID ' . $requestDetails['order_id'];
            $result = $walletModel->insertWalletTransaction($requestDetails['user_id'], $requestDetails['total_amount'], $actualWalletAmount, 'credit', $remarkForUserWallet);
            if ($result) {
                $userModel = new UserModel();
                $userModel->set(['wallet' => $actualWalletAmount])->update($requestDetails['user_id']);
            }
            $output = ['success' => true, "message" => "Item returned successfully"];
        } else {
            $output = ['success' => false, "message" => "Something went wrong"];
        }

        return $this->response->setJSON($output);
    }
}
