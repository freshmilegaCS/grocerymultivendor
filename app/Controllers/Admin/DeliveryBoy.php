<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CityModel;
use App\Models\CountryModel;
use App\Models\DeliveryBoyFundTransferModel;
use App\Models\DeliveryBoyModel;
use App\Models\DeliveryBoyTransactionModel;
use App\Models\SettingsModel;

class DeliveryBoy extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_add('delivery-boy')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            $cityModel = new CityModel();
            $data['city'] = $cityModel->getAllCity();

            return view('/deliveryBoy/add', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function view()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('delivery-boy')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('/deliveryBoy/list', $data);
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
        if (!can_view('delivery-boy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $deliveryBoyModel = new DeliveryBoyModel();
        $delivery_boys = $deliveryBoyModel->getAllDeliveryBoys();
        $output = ['data' => []];
        foreach ($delivery_boys as $deliveryBoy) {
            $status = $deliveryBoy['status'] == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            $isAvailable = $deliveryBoy['is_available'] == 1 ? '<span class="badge badge-success">Available</span>' : '<span class="badge badge-danger">Not Available</span>';

            $bonusDetails = $deliveryBoy['bonus_type'] == 1
                ? "<p>Commission {$deliveryBoy['bonus_percentage']}%</p>
                   <ul>
                       <li>Min Amt: {$deliveryBoy['bonus_min_amount']}</li>
                       <li>Max Amt: {$deliveryBoy['bonus_max_amount']}</li>
                   </ul>"
                : '<p>Fixed</p>';

            $actions = '
                <a href="' . base_url('admin/delivery_boy/edit/' . $deliveryBoy['id']) . '" class="btn btn-primary-light btn-xs" data-tooltip="tooltip" title="Edit">
                    <i class="fi fi-tr-customize-edit"></i>
                </a>
                <button type="button" class="btn btn-danger-light btn-xs" data-tooltip="tooltip" title="Delete" onclick="deleteDeliveryBoy(' . $deliveryBoy['id'] . ')">
                    <i class="fi fi-tr-trash-xmark"></i>
                </button>';

            $output['data'][] = [
                $deliveryBoy['id'],
                esc($deliveryBoy['name']),
                esc($deliveryBoy['mobile']),
                esc($deliveryBoy['address']),
                esc($deliveryBoy['city_name']),
                $bonusDetails,
                esc($deliveryBoy['balance']),
                esc($deliveryBoy['cash_collection_amount']), 
                $status,
                $isAvailable,
                $actions
            ];
        }

        return $this->response->setJSON($output);
    }

    public function add()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('delivery-boy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $deliveryBoyModel = new DeliveryBoyModel();

        $this->validate([
            'name' => 'required|min_length[3]|max_length[50]',
            'mobile' => 'required|numeric|min_length[10]|max_length[15]|is_unique[delivery_boy.mobile]',
            'address' => 'required',
            'city_id' => 'required',
            'national_identity_card' => 'uploaded[national_identity_card]|max_size[national_identity_card,2048]|ext_in[national_identity_card,png,jpg,jpeg,pdf]',
            'driving_license' => 'uploaded[driving_license]|max_size[driving_license,2048]|ext_in[driving_license,png,jpg,jpeg,pdf]',
        ]);
        $settingModel = new SettingsModel();
        $appSetting = $settingModel->getSettings();
        if ($appSetting['delivery_boy_bonus_setting'] == 1) {

            $data = [
                'admin_id' => session()->get('user_id'), // Assuming you have an admin session
                'city_id' => $this->request->getPost('city_id'),
                'name' => $this->request->getPost('name'),
                'mobile' => $this->request->getPost('mobile'),
                'address' => $this->request->getPost('address'),
                'dob' => $this->request->getPost('dob'),
                'bank_account_number' => $this->request->getPost('bank_account_number'),
                'bank_name' => $this->request->getPost('bank_name'),
                'account_name' => $this->request->getPost('account_name'),
                'ifsc_code' => $this->request->getPost('ifsc_code'),
                'pincode' => $this->request->getPost('pincode'),
                'other_payment_information' => $this->request->getPost('other_payment_information'),
                'bonus_type' => $this->request->getPost('bonus_type'),
                'bonus_percentage' => $this->request->getPost('bonus_percentage')  ?? 0,
                'bonus_min_amount' => $this->request->getPost('bonus_min_amount')  ?? 0,
                'bonus_max_amount' => $this->request->getPost('bonus_max_amount')  ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
            ];
        } else {
            $data = [
                'admin_id' => session()->get('user_id'), // Assuming you have an admin session
                'city_id' => $this->request->getPost('city_id'),
                'name' => $this->request->getPost('name'),
                'mobile' => $this->request->getPost('mobile'),
                'address' => $this->request->getPost('address'),
                'dob' => $this->request->getPost('dob'),
                'bank_account_number' => $this->request->getPost('bank_account_number'),
                'bank_name' => $this->request->getPost('bank_name'),
                'account_name' => $this->request->getPost('account_name'),
                'ifsc_code' => $this->request->getPost('ifsc_code'),
                'pincode' => $this->request->getPost('pincode'),
                'other_payment_information' => $this->request->getPost('other_payment_information'),
                'bonus_type' => $this->request->getPost('bonus_type'),
                'bonus_percentage' =>  0,
                'bonus_min_amount' =>  0,
                'bonus_max_amount' =>  0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
            ];
        }

        // Handle file uploads
        if ($files = $this->request->getFiles()['national_identity_card']) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/delivery_boy/national_id/', $newName);
                    $data['national_identity_card'] = '/uploads/delivery_boy/national_id/' . $newName;
                }
            }
        }


        if ($files = $this->request->getFiles()['driving_license']) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/delivery_boy/driving_license/', $newName);
                    $data['driving_license'] = '/uploads/delivery_boy/driving_license/' . $newName;
                }
            }
        }

        if ($deliveryBoyModel->insert($data)) {
            $output['success'] = true;
            $output['message'] = 'Delivery boy added successfully';
        } else {
            $output['success'] = false;
            $output['message'] = 'Something went wrong';
        }
        return $this->response->setJSON($output);
    }

    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('delivery-boy')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            $deliveryBoyModel = new DeliveryBoyModel();
            // Fetch delivery boy details by ID
            $deliveryBoy = $deliveryBoyModel->getDeliveryBoyById($id);
            $data['deliveryBoy'] = $deliveryBoy;
            $cityModel = new CityModel();
            $data['city'] = $cityModel->getAllCity();

            return view('/deliveryBoy/edit', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function delete()
    {
        $response = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('delivery-boy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Validate that `delivery_boy_id` is present in the POST data
        $deliveryBoyId = $this->request->getPost('delivery_boy_id');

        if ($deliveryBoyId) {
            $deliveryBoyModel = new DeliveryBoyModel();

            // Soft delete the delivery boy
            if ($deliveryBoyModel->softDeleteDeliveryBoy($deliveryBoyId)) {
                $response['success'] = true;
                $response['message'] = 'Delivery Boy deleted successfully!';
            } else {
                $response['message'] = 'Error While deleting Delivery Boy';
            }
        }

        // Return the response as JSON
        return $this->response->setJSON($response);
    }

    public function update()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('delivery-boy')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $deliveryBoyModel = new DeliveryBoyModel();

        $this->validate([
            'name' => 'required|min_length[3]|max_length[50]',
            'mobile' => 'required|numeric|min_length[10]|max_length[15]|is_unique[delivery_boy.mobile]',
            'address' => 'required',
            'national_identity_card' => 'uploaded[national_identity_card]|max_size[national_identity_card,2048]|ext_in[national_identity_card,png,jpg,jpeg,pdf]',
            'driving_license' => 'uploaded[driving_license]|max_size[driving_license,2048]|ext_in[driving_license,png,jpg,jpeg,pdf]',
        ]);
        $settingModel = new SettingsModel();
        $appSetting = $settingModel->getSettings();
        if ($appSetting['delivery_boy_bonus_setting'] == 1) {

            $data = [
                'city_id' => $this->request->getPost('city_id'),
                'name' => $this->request->getPost('name'),
                'mobile' => $this->request->getPost('mobile'),
                'address' => $this->request->getPost('address'),
                'dob' => $this->request->getPost('dob'),
                'bank_account_number' => $this->request->getPost('bank_account_number'),
                'bank_name' => $this->request->getPost('bank_name'),
                'account_name' => $this->request->getPost('account_name'),
                'ifsc_code' => $this->request->getPost('ifsc_code'),
                'pincode' => $this->request->getPost('pincode'),
                'other_payment_information' => $this->request->getPost('other_payment_information'),
                'bonus_type' => $this->request->getPost('bonus_type'),
                'bonus_percentage' => $this->request->getPost('bonus_percentage') ?? 0,
                'bonus_min_amount' => $this->request->getPost('bonus_min_amount') ?? 0,
                'bonus_max_amount' => $this->request->getPost('bonus_max_amount') ?? 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        } else {
            $data = [
                'city_id' => $this->request->getPost('city_id'),
                'name' => $this->request->getPost('name'),
                'mobile' => $this->request->getPost('mobile'),
                'address' => $this->request->getPost('address'),
                'dob' => $this->request->getPost('dob'),
                'bank_account_number' => $this->request->getPost('bank_account_number'),
                'bank_name' => $this->request->getPost('bank_name'),
                'account_name' => $this->request->getPost('account_name'),
                'ifsc_code' => $this->request->getPost('ifsc_code'),
                'pincode' => $this->request->getPost('pincode'),
                'other_payment_information' => $this->request->getPost('other_payment_information'),
                'bonus_type' => $this->request->getPost('bonus_type'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        // Handle file uploads
        if (isset($this->request->getFiles()['national_identity_card'])) {
            if ($files = $this->request->getFiles()['national_identity_card']) {
                foreach ($files as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move('uploads/delivery_boy/national_id/', $newName);
                        $data['national_identity_card'] = '/uploads/delivery_boy/national_id/' . $newName;
                    }
                }
            }
        }
        if (isset($this->request->getFiles()['driving_license'])) {
            if ($files = $this->request->getFiles()['driving_license']) {
                foreach ($files as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move('uploads/delivery_boy/driving_license/', $newName);
                        $data['driving_license'] = '/uploads/delivery_boy/driving_license/' . $newName;
                    }
                }
            }
        }

        if ($deliveryBoyModel->set($data)->where('id', $this->request->getPost('editid'))->update()) {
            $output['success'] = true;
            $output['message'] = 'Delivery boy updated successfully';
        } else {
            $output['success'] = false;
            $output['message'] = 'Something went wrong';
        }
        return $this->response->setJSON($output);
    }

    public function fundTransfer()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('delivery-boy-fund-transfer')) {
                return redirect()->to('admin/permission-not-allowed');
            }

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();


            $deliveryBoyModel = new DeliveryBoyModel();
            $data['deliveryBoys'] = $deliveryBoyModel->select('id, name, mobile, balance, cash_collection_amount')->where('is_delete', 0)->findAll();

            $countryModel = new CountryModel();
            $data['country'] = $countryModel->where('is_active', 1)->first();

            return view('/deliveryBoy/fundTransfer', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function listFundTransfer()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_view('delivery-boy-fund-transfer')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $deliveryBoyFundTransferModel = new DeliveryBoyFundTransferModel();
        // Get input filters
        $txn_date = $this->request->getPost('txn_date');
        $delivery_boy = $this->request->getPost('delivery_boy');
        $method = $this->request->getPost('method');
        if (empty($txn_date)) {
            $today = date('Y-m-d');
            $txn_date = "$today - $today";
        }
        $dates = explode(' - ', $txn_date);

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        $builder = $deliveryBoyFundTransferModel->select(
            'delivery_boy_fund_transfer.id, delivery_boy_fund_transfer.delivery_boy_id, delivery_boy_fund_transfer.type, delivery_boy_fund_transfer.opening_balance, delivery_boy_fund_transfer.closing_balance, delivery_boy_fund_transfer.amount, delivery_boy_fund_transfer.message, delivery_boy_fund_transfer.created_at,delivery_boy_fund_transfer.updated_at, delivery_boy.name, delivery_boy.mobile, delivery_boy.id as delivery_boy_id'
        )
            ->join('delivery_boy', 'delivery_boy.id = delivery_boy_fund_transfer.delivery_boy_id', 'left');

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(delivery_boy_fund_transfer.created_at) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(delivery_boy_fund_transfer.created_at) <=', date('Y-m-d', strtotime($dates[1])));
        }
        if (!empty($delivery_boy)) {
            $builder->where('delivery_boy_fund_transfer.delivery_boy_id', $delivery_boy);
        }
        if (!empty($method)) {
            $builder->where('delivery_boy_fund_transfer.type', $method);
        }

        // Fetch data
        $query = $builder->get();
        $fundtransferLists = $query->getResultArray();
        $output['data'] = [];
        foreach ($fundtransferLists as $index => $fundtransferList) {
            if ($fundtransferList['type'] == 'credit') {
                $type = '<span class="badge badge-success">Credit</span>';
            } else {
                $type = '<span class="badge badge-danger">Debit</span>';
            }
            $output['data'][] = [
                $fundtransferList['id'],
                $fundtransferList['name'],
                $fundtransferList['mobile'],
                $country['currency_symbol'] . " " . round($fundtransferList['opening_balance'], 2),
                $country['currency_symbol'] . " " . round($fundtransferList['closing_balance'], 2),
                $country['currency_symbol'] . " " . round($fundtransferList['amount'], 2),
                $type,
                $fundtransferList['message'],
                $fundtransferList['created_at'],

            ];
        }
        return $this->response->setJSON($output);
    }

    public function addFundTransfer()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_add('delivery-boy-fund-transfer')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        
        $deliveryBoyFundTransferModel = new DeliveryBoyFundTransferModel();
        // Get input filters
        $delivery_boy_id = $this->request->getPost('delivery_boy_id');
        $amount = $this->request->getPost('amount');
        $remark = $this->request->getPost('remark');

        $deliveryBoyModel = new DeliveryBoyModel();
        $delivery_boy = $deliveryBoyModel->where('is_delete', 0)->where('id', $delivery_boy_id)->first();

        if ($delivery_boy['balance'] >= $amount) {
            $new_opening_balance = $delivery_boy['balance'];
            $new_closing_balance = $delivery_boy['balance'] - $amount;
            $data = [
                'delivery_boy_id' => $delivery_boy_id,
                'type' => 'debit',
                'opening_balance' => $new_opening_balance,
                'closing_balance' => $new_closing_balance,
                'amount' => $amount,
                'message' => $remark,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $success = $deliveryBoyFundTransferModel->insert($data);
            if ($success) {
                $deliveryBoyModel->set(['balance' => $new_closing_balance])->where('id', $delivery_boy_id)->update();

                $deliveryBoys = $deliveryBoyModel->select('id, name, balance')->where('is_delete', 0)->findAll();

                $countryModel = new CountryModel();
                $country = $countryModel->where('is_active', 1)->first();

                $output = ['success' => true, "message" => "Fund trasnfer done successfully", "deliveryBoy" => $deliveryBoys, "currency" => $country['currency_symbol']];
            } else {
                $output = ['success' => false, "message" => "Something went wrong"];
            }
        } else {
            $output = ['success' => false, "message" => "Amount should be equal or greater than balance."];
        }

        return $this->response->setJSON($output);
    }

    public function cashCollection()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('delivery-boy-cash-collection')) {
                return redirect()->to('admin/permission-not-allowed');
            }

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            $deliveryBoyModel = new DeliveryBoyModel();
            $data['deliveryBoys'] = $deliveryBoyModel->select('id, name, mobile, balance, cash_collection_amount')->where('is_delete', 0)->findAll();

            $countryModel = new CountryModel();
            $data['country'] = $countryModel->where('is_active', 1)->first();

            return view('/deliveryBoy/cashCollection', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function listCashCollection()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_view('delivery-boy-cash-collection')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $deliveryBoyTransactionModel = new DeliveryBoyTransactionModel();
        // Get input filters
        $txn_date = $this->request->getPost('txn_date');
        $delivery_boy = $this->request->getPost('delivery_boy');
        $method = $this->request->getPost('method');
        if (empty($txn_date)) {
            $today = date('Y-m-d');
            $txn_date = "$today - $today";
        }
        $dates = explode(' - ', $txn_date);

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        $builder = $deliveryBoyTransactionModel->select(
            'delivery_boy_transaction.id, delivery_boy_transaction.order_id, delivery_boy_transaction.delivery_boy_id, delivery_boy_transaction.type, delivery_boy_transaction.amount, delivery_boy_transaction.message, delivery_boy_transaction.created_at, delivery_boy.name, delivery_boy.id as delivery_boy_id, orders.subtotal, orders.tax, orders.delivery_charge, orders.used_wallet_amount, orders.coupon_amount, orders.additional_charge'
        )
            ->join('delivery_boy', 'delivery_boy.id = delivery_boy_transaction.delivery_boy_id', 'left')
            ->join('orders', 'orders.id = delivery_boy_transaction.order_id', 'left');

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(delivery_boy_transaction.created_at) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(delivery_boy_transaction.created_at) <=', date('Y-m-d', strtotime($dates[1])));
        }
        if (!empty($delivery_boy)) {
            $builder->where('delivery_boy_transaction.delivery_boy_id', $delivery_boy);
        }
        if (!empty($method)) {
            $builder->where('delivery_boy_transaction.type', $method);
        }


        // Fetch data
        $query = $builder->get();
        $cashCollectionLists = $query->getResultArray();
        $output['data'] = [];
        foreach ($cashCollectionLists as $index => $cashCollectionList) {
            if ($cashCollectionList['type'] == 'credit') {
                $type = '<span class="badge badge-success">Credit</span>';
            } else {
                $type = '<span class="badge badge-danger">Debit</span>';
            }
            $orderTotal = round($cashCollectionList['subtotal'], 2) + round($cashCollectionList['tax'], 2) + round($cashCollectionList['delivery_charge'], 2) + round($cashCollectionList['additional_charge'], 2) - round($cashCollectionList['used_wallet_amount'], 2) - round($cashCollectionList['coupon_amount'], 2);
            $output['data'][] = [
                $cashCollectionList['id'],
                $cashCollectionList['name'],
                $cashCollectionList['order_id'],
                $country['currency_symbol'] . " " . $orderTotal,
                $country['currency_symbol'] . " " . round($cashCollectionList['amount'], 2),
                $type,
                $cashCollectionList['message'],
                $cashCollectionList['created_at'],

            ];
        }
        return $this->response->setJSON($output);
    }
    public function addCashCollection()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_add('delivery-boy-cash-collection')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        

        $deliveryBoyTransactionModel = new DeliveryBoyTransactionModel();
        // Get input filters
        $delivery_boy_id = $this->request->getPost('delivery_boy_id');
        $amount = $this->request->getPost('amount');
        $remark = $this->request->getPost('remark');

        $deliveryBoyModel = new DeliveryBoyModel();
        $delivery_boy = $deliveryBoyModel->where('is_delete', 0)->where('id', $delivery_boy_id)->first();

        if ($delivery_boy['cash_collection_amount'] >= $amount) {
            $new_closing_balance = $delivery_boy['cash_collection_amount'] - $amount;
            $data = [
                'delivery_boy_id' => $delivery_boy_id,
                'type' => 'debit',
                'amount' => $amount,
                'message' => $remark,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $success = $deliveryBoyTransactionModel->insert($data);
            if ($success) {
                $deliveryBoyModel->set(['cash_collection_amount' => $new_closing_balance])->where('id', $delivery_boy_id)->update();

                $deliveryBoys = $deliveryBoyModel->select('id, name, cash_collection_amount')->where('is_delete', 0)->findAll();

                $countryModel = new CountryModel();
                $country = $countryModel->where('is_active', 1)->first();

                $output = ['success' => true, "message" => "Cash collection done successfully", "deliveryBoy" => $deliveryBoys, "currency" => $country['currency_symbol']];
            } else {
                $output = ['success' => false, "message" => "Something went wrong"];
            }
        } else {
            $output = ['success' => false, "message" => "Amount should be equal or greater than cash collection amount."];
        }

        return $this->response->setJSON($output);
    }
}
