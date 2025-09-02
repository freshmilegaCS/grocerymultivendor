<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\PaymentMethodModel;
use App\Models\SettingsModel;

class PaymentMethod extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('payment-setting')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $PaymentMethodModel = new PaymentMethodModel();
            $paymentMethods = $PaymentMethodModel->getAllPaymentMethods();

            return view('paymentMethod', [
                'settings' => $settingModel->getSettings(),
                'paymentSettings' => $paymentMethods
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }


    public function update()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {

            if (!can_edit('payment-setting')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            if ($this->settings['demo_mode']) {
                return redirect()->back()->with('error', 'Demo Mode! Permission not allowed');
            }

            $paymentMethodModel = new PaymentMethodModel();

            $description = $this->request->getPost('description');
            $api_key = $this->request->getPost('api_key');
            $secret_key = $this->request->getPost('secret_key');
            $id = $this->request->getPost('payment_method_id');
            $status = $this->request->getPost('status');

            if ($id == 1) {
                $data = [
                    'description' => $description,
                    'status' => $status
                ];
            } else {
                $data = [
                    'description' => $description,
                    'api_key' => $api_key,
                    'secret_key' => $secret_key,
                    'status' => $status
                ];
            }



            $success = $paymentMethodModel->updateMethod($id, $data);

            if ($success) {
                return redirect()->to('admin/payment')->with('success', 'Payment setting updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to update.');
            }
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

}
