<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\CouponModel;
use App\Models\DeviceTokenModel;
use App\Models\SettingsModel;

class Coupon extends BaseController
{
    protected $couponModel;
    public function __construct()
    {
        $this->couponModel = new CouponModel();
    }
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('coupon')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();


            return view('coupon', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function list()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('coupon')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $couponModel = new CouponModel(); 
        $coupons = $couponModel->getCoupons();
        $output['data'] = [];
        $x = 1;

        foreach ($coupons as $row) {
            $img = "<a href='" . base_url($row['coupon_img']) . "' target='_blank'><img class='media-object round-media' src='" . base_url($row['coupon_img']) . "' alt='image' style='height: 40px;width:40px'></a><b> {$row['coupon_title']}</b>";
            $action = " 
                       <a type='button' data-tooltip='tooltip' title='Delete Coupon' onclick='deletecoupon({$row['id']})' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'></i></a>";

            $status = $row['status'] == 1
                ? "<span class='badge badge-success'>Published</span>"
                : "<span class='badge badge-danger'>Unpublish</span>";

            if ($row['user_id'] == 0) {
                $type = $row['is_multitimes'] == 0
                    ? "Applicable for All Users <b>Single Time</b>"
                    : "Applicable for All Users <b>Multi Time</b>";
            } else {
                $type = $row['is_multitimes'] == 0
                    ? "Only applicable for {$row['user_name']} <b>Single Time</b>"
                    : "Applicable for All Users <b>Multi Time</b>";
            }

            if ($row['coupon_type'] == 1) {
                $discounttype = "Percentage";
            } else {
                $discounttype ="Value";
            }

            $output['data'][] = [
                $x,
                $img,
                $row['coupon_code'],
                $row['value'],
                $type,
                $discounttype,
                $row['date'],
                $status,
                $row['min_order_amount'],
                $action
            ];
            $x++;
        }

        return $this->response->setJSON($output);
    }
    public function add()
    {
        helper('firebase_helper');

        $output = ['success' => false];

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('coupon')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        // Validate incoming request data
        $validation = \Config\Services::validation();
        $validation->setRules([
            'user_type'      => 'required|in_list[0,1]',
            'user_id'        => 'permit_empty|integer',
            'n_use'          => 'required|integer',
            'coupon_code'    => 'required|string',
            'exp_date'       => 'required|valid_date',
            'min_amt'        => 'required|numeric',
            'coupon_title'   => 'required|string',
            'coupon_status'  => 'required|in_list[0,1]',
            'coupon_type'  => 'required|in_list[1,2]',
            'coupon_value'   => 'required|numeric',
            'description'    => 'required|string',
            'coupon_img'     => 'required|string'
        ]);

        if (!$validation->withRequest($this->request)->run()) {

            $output['message'] = $validation->getErrors();
            return $this->response->setJSON($output);
        }

        // Prepare data
        $user_id = $this->request->getPost('user_type') == 1 ? $this->request->getPost('user_id') : 0;
        $data = [
            'user_id'          => $user_id,
            'is_multitimes'    => $this->request->getPost('n_use'),
            'coupon_code'      => $this->request->getPost('coupon_code'),
            'date'             => $this->request->getPost('exp_date'),
            'min_order_amount' => $this->request->getPost('min_amt'),
            'coupon_title'     => $this->request->getPost('coupon_title'),
            'status'           => $this->request->getPost('coupon_status'),
            'value'            => $this->request->getPost('coupon_value'),
            'coupon_type'      => $this->request->getPost('coupon_type'),
            'description'      => $this->request->getPost('description')
        ];

        // Handle image upload
        $coupon_img = $this->request->getPost('coupon_img');
        list(, $coupon_img) = explode(';', $coupon_img);
        list(, $coupon_img) = explode(',', $coupon_img);
        $coupon_img = base64_decode($coupon_img);

        // Use FCPATH for the public directory
        $db_file_path = 'uploads/coupon/coupon_' . time() . '.webp';
        $full_file_path = FCPATH . $db_file_path;

        // Create directory if it doesn't exist
        if (!is_dir(dirname($full_file_path))) {
            mkdir(dirname($full_file_path), 0777, true);
        }

        if (file_put_contents($full_file_path, $coupon_img) !== false) {
            $data['coupon_img'] = $db_file_path;

            // Insert data into database
            if ($this->couponModel->insertCoupon($data)) {
                if ($user_id) {
                    $deviceTokenModel = new DeviceTokenModel();
                    $userToken = $deviceTokenModel->where('user_type', 2)->where('user_id', $user_id)->orderBy('id', 'desc')->first();
                    $dataForNotification = [
                        'screen' => 'Notification',
                    ];
                    // sendFirebaseNotification($userToken['app_key'], 'Congratulation Special Coupon For You', 'Use coupon code ' . $this->request->getPost('coupon_code') . ' for more discount', $dataForNotification);
                }
                $output['success'] = true;
                $output['message'] = 'Coupon added successfully';
            }
        }
        return $this->response->setJSON($output);
    }
    public function delete()
    {
        $output = ['success' => false];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('coupon')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Validate the incoming request data
        $validation = \Config\Services::validation();
        $validation->setRules([
            'c_id' => 'required|integer'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $output['message'] = $validation->getErrors();
            return $this->response->setJSON($output);
        }

        $couponId = $this->request->getPost('c_id');

        // Attempt to soft delete the coupon
        if ($this->couponModel->deleteCoupon($couponId)) {
            $output['success'] = true;
            $output['message'] = 'Coupon deleted successfully!';
        }

        return $this->response->setJSON($output);
    }
}
