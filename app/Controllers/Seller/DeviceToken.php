<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;

use App\Models\DeviceTokenModel;

class DeviceToken extends BaseController
{
    public function tokenUpdate()
    {
        if (!session()->has('user_id') || session('account_type') != 'Seller') {
            return redirect()->to('seller/login'); // Redirect to login if session is not set
        }

        $token = $this->request->getPost('token');
        $deviceTokenModel = new DeviceTokenModel();
        $data = [
            'user_type' => 4,  //for seller
            'user_id' => session()->get('user_id'),
            'app_key' => $token
        ];

        $query = $deviceTokenModel
            ->where('user_type', 4)
            ->where('app_key', $token)
            ->get();

        if ($query->getNumRows() > 0) {
            return $this->response->setJSON(['success' => true, 'message' => 'Token already exist']);
        }


        $success = $deviceTokenModel->insert($data);

        // Prepare the response
        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'Notification token updated']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update token']);
        }
    }
}
