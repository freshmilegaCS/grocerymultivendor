<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\CountryModel;
use App\Models\DeliverableAreaModel;
use App\Models\OrderProductModel;
use App\Models\SellerCategoriesModel;
use App\Models\SellerModel;
use App\Models\SellerWalletTransactionModel;
use App\Models\SettingsModel;

class Seller extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_add('seller') ||  !can_view('seller')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $data['countrySetting'] = $this->country;
            $data['timeZoneSetting'] = $this->timeZone;
            $cityModel = new CityModel();
            $data['city'] = $cityModel->getAllCity();
            $categoryModel = new CategoryModel();
            $data['categories'] = $categoryModel->orderBy('row_order')->findAll();
            return view('/seller/addSeller', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function add()
    {
        $output = ['success' => false, 'msg' => ''];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_add('seller')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $sellerModel = new SellerModel();

        $profilePic = '';
        $addressProof = '';
        $nationalIdProof = '';

        $seller = $sellerModel->where('email', $this->request->getPost('email'))->where('is_delete', 0)->countAllResults();

        if ($seller > 0) {
            $output['msg'] = "This user email already exists";
        } else {

            // Generate the initial slug
            $slug_prev = str_replace(" ", "-", $this->request->getPost('store_name'));
            $slug = preg_replace('/[^A-Za-z0-9-]/', '', strtolower($slug_prev));
            $slug1 = $slug;
            $check = true;
            $x = 1;
            while ($check) {
                $duplicateSlug = $sellerModel->where('slug', $slug1)->countAllResults();
                if ($duplicateSlug > 0) {
                    $slug1 = $slug . $x;
                } else {
                    $check = false;
                }
                $x++;
            }


            $profilePic = '';
            $logoFile = $this->request->getFile('logo');
            if ($logoFile->isValid() && !$logoFile->hasMoved()) {
                $profilePic =  'profile_' . rand(10000000, 99999999) . time() . $logoFile->getClientName();
                if ($logoFile->move('uploads/seller/', $profilePic)) {
                    $profilePic = 'uploads/seller/' . $profilePic;
                } else {
                    // Error moving file
                    $errors = $logoFile->getErrorString();
                    $output['msg1'] = "Failed to move profile picture: $errors";
                }
            } else {
                // Invalid file
                $output['msg11'] = "Invalid profile picture";
            }

            // Handle address proof upload
            $addressProof = '';
            $addressProofFile = $this->request->getFile('address_proof');
            if ($addressProofFile->isValid() && !$addressProofFile->hasMoved()) {
                $addressProof =  'address_proof_' . rand(10000000, 99999999) . time() . $addressProofFile->getClientName();
                if ($addressProofFile->move('uploads/seller/', $addressProof)) {
                    $addressProof = 'uploads/seller/' . $addressProof;
                } else {
                    // Error moving file
                    $errors = $addressProofFile->getErrorString();
                    $output['msg2'] = "Failed to move address proof: $errors";
                }
            } else {
                // Invalid file
                $output['msg22'] = "Invalid address proof";
            }

            // Handle national ID proof upload
            $nationalIdProof = '';
            $nationalIdProofFile = $this->request->getFile('national_id_proof');
            if ($nationalIdProofFile->isValid() && !$nationalIdProofFile->hasMoved()) {
                $nationalIdProof =  'national_id' . rand(10000000, 99999999) . time() . $nationalIdProofFile->getClientName();
                if ($nationalIdProofFile->move('uploads/seller/', $nationalIdProof)) {
                    $nationalIdProof = 'uploads/seller/' . $nationalIdProof;
                } else {
                    // Error moving file
                    $errors = $nationalIdProofFile->getErrorString();
                    $output['msg3'] = "Failed to move national ID proof: $errors";
                }
            } else {
                // Invalid file
                $output['msg33'] = "Invalid national ID proof";
            }

            $sellerData = [
                'email' => $this->request->getPost('email'),
                'store_name' => $this->request->getPost('store_name'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'mobile' => $this->request->getPost('mobile'),
                'slug'  => $slug1,
                'city_id' => $this->request->getPost('city_id'),
                'deliverable_area_id' => $this->request->getPost('deliverable_area_id'),
                'pan_number' => $this->request->getPost('pan_number'),
                'tax_name' => $this->request->getPost('tax_name'),
                'tax_number' => $this->request->getPost('tax_number'),
                'require_products_approval' => $this->request->getPost('require_products_approval'),
                'view_customer_details' => $this->request->getPost('view_customer_details'),
                'commission' => $this->request->getPost('commission'),
                'store_address' => $this->request->getPost('store_address'),
                'account_number' => $this->request->getPost('account_number'),
                'account_name' => $this->request->getPost('account_name'),
                'bank_ifsc_code' => $this->request->getPost('bank_ifsc_code'),
                'bank_name' => $this->request->getPost('bank_name'),
                'branch' => $this->request->getPost('branch'),
                'logo' => $profilePic,
                'address_proof' => $addressProof,
                'national_id_proof' => $nationalIdProof,
                'name' => $this->request->getPost('name'),
                'map_address' => $this->request->getPost('map_address'),
                'latitude' => $this->request->getPost('latitude'),
                'longitude' => $this->request->getPost('longitude'),
                'status' => 1,
            ];

            $sellerid =  $sellerModel->insert($sellerData);

            if ($sellerid) {
                $data = [];
                foreach ($this->request->getPost('category') as $categoryId) {
                    $data[] = [
                        'seller_id' => $sellerid,
                        'category_id' => $categoryId
                    ];
                }

                $sellerCategoryModel = new SellerCategoriesModel();
                $sellerCategoryModel->insertBatch($data);

                $output['success'] = true;
                $output['msg'] = "Seller added successfully";
            } else {
                $output['success'] = false;
                $output['msg'] = "Something went wrong";
            }
        }


        return $this->response->setJSON($output);
    }

    public function view()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('seller')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('seller/list', $data);
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
        if (!can_view('seller')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }


        $sellerModel = new SellerModel();
        $sellers = $sellerModel->where('is_delete', 0)->findAll();
        $output['data'] = [];
        $sellerCategoriesModel = new SellerCategoriesModel();
        foreach ($sellers as $row) {
            $img = "<a href='" . base_url($row['logo']) . "' target='_blank'>
                    <img class='media-object round-media' src='" . base_url($row['logo']) . "' alt='image' style='height: 60px; width: 40%'>
                </a>";
            $action = "<a data-tooltip='tooltip' title='Edit Seller' href='" . base_url("admin/seller/edit/{$row['id']}") . "' class='btn btn-primary-light  btn-xs'>
                    <i class='fi fi-tr-customize-edit'></i></a>
                   <a type='button' data-tooltip='tooltip' title='Delete Seller' onclick='deleteSeller({$row['id']})' class='btn btn-danger-light btn-xs'>
                    <i class='fi fi-tr-trash-xmark'></i></a>";

            $seller_categories = $sellerCategoriesModel->select('category.category_name, seller_categories.id')->join('category', 'category.id = seller_categories.category_id')->where('seller_categories.seller_id', $row['id'])->findAll();
            $categories = '';
            foreach ($seller_categories as $seller_category) {
                $categories .= "<span class='m-1 badge badge-success'>" . $seller_category['category_name'] . " <button class='btn btn-secondary btn-xxs' onclick='deleteSellerCategory(" . $row['id'] . ", " . $seller_category['id'] . ")'>x</button></span> ";
            }

            // Publish status
            if ($row['status'] == 0) {
                $status = "<span class='badge badge-warning'>Registered</span> ";
            } elseif ($row['status'] == 1) {
                $status = "<span class='badge badge-success'>Approved</span> ";
            } elseif ($row['status'] == 2) {
                $status = "<span class='badge badge-danger' data-tooltip='tooltip' data-title='" . $row['status_reason'] . "'>Rejected</span> ";
            }

            if ($row['require_products_approval'] == 0) {
                $require_products_approval = "<span class='badge badge-danger'>No</span> ";
            } else {
                $require_products_approval = "<span class='badge badge-success'>Yes</span> ";
            }

            // Prepare the output data
            $output['data'][] = [
                $row['id'],
                $row['name'],
                $row['store_name'],
                $row['mobile'] . '<br>' . $row['email'],
                $img,
                $row['balance'],
                $row['commission'] . '%',
                $categories, // Subcategory name
                $status,
                $require_products_approval,
                $action
            ];
        }

        return $this->response->setJSON($output);
    }
    public function topSellerList()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('seller')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $output['data'] = [];
        $sellerWalletTransactionModel = new SellerWalletTransactionModel();

        $sellers = $sellerWalletTransactionModel->select('seller.id, seller.name, seller.store_name, SUM(seller_wallet_transaction.amount) as total_earnings')
            ->join('seller', 'seller.id = seller_wallet_transaction.seller_id')
            ->where('seller_wallet_transaction.type', 'credit')
            ->where('seller.is_delete', 0)
            ->groupBy('seller.id')
            ->orderBy('total_earnings', 'DESC')
            ->limit(20)
            ->findAll();
        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        foreach ($sellers as $row) {
            $action = "<a data-tooltip='tooltip' title='View Seller' href='" . base_url("admin/seller/list") . "' class='btn btn-primary-light  btn-xs'>
                    <i class='fi fi-tr-magnifying-glass-eye'></i></a>";

            // Stock status



            // Prepare the output data
            $output['data'][] = [
                $row['id'],
                $row['name'],
                $row['store_name'],
                $country['currency_symbol'] . " " . round($row['total_earnings'], 2),
                $action
            ];
        }

        return $this->response->setJSON($output);
    }
    public function delete()
    {
        $output = ['success' => false];

        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('seller')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        
        // Check if seller_id is set in the POST request
        if ($this->request->getPost('seller_id')) {
            // Load the database connection
            $sellerModel = new SellerModel();
            $sellerId = $this->request->getPost('seller_id');
            $update = $sellerModel->update($sellerId, ['is_delete' => 1]);

            // Check if the update was successful
            if ($update) {
                $output['success'] = true;
                $output['message'] = 'Seller deleted successfully';
            } else {
                $output['message'] = 'Something went wrong';
            }
        }

        // Return the output as JSON
        return $this->response->setJSON($output);
    }

    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('seller')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $cityModel = new CityModel();
            $data['city'] = $cityModel->getAllCity();
            $categoryModel = new CategoryModel();
            $data['categories'] = $categoryModel->orderBy('row_order')->findAll();
            $sellerModel = new SellerModel();
            $data['seller'] = $sellerModel->where('id', $id)->first();

            $sellerCategoryModel = new SellerCategoriesModel();
            $data['seller_categories'] = $sellerCategoryModel->where('seller_id', $id)->findAll();

            $DeliverableAreaModel = new DeliverableAreaModel();
            $data['deliverable_area'] = $DeliverableAreaModel->where('id', $data['seller']['deliverable_area_id'])->find();

            return view('seller/editSeller', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function update()
    {
        $output = ['success' => false, 'msg' => ''];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_edit('seller')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $sellerModel = new SellerModel();

        $profilePic = '';
        $addressProof = '';
        $nationalIdProof = '';

        $profilePic = '';
        $logoFile = $this->request->getFile('logo');
        if ($logoFile->isValid() && !$logoFile->hasMoved()) {
            $profilePic =  'profile_' . rand(10000000, 99999999) . time() . $logoFile->getClientName();
            if ($logoFile->move('uploads/seller/', $profilePic)) {
                $profilePic = 'uploads/seller/' . $profilePic;
                $data3 = [
                    'logo' => $profilePic,
                ];
                $sellerid =  $sellerModel->set($data3)->where('id', $this->request->getPost('seller_id'))->update();
            } 
        }

        // Handle address proof upload
        $addressProof = '';
        $addressProofFile = $this->request->getFile('address_proof');
        if ($addressProofFile->isValid() && !$addressProofFile->hasMoved()) {
            $addressProof =  'address_proof_' . rand(10000000, 99999999) . time() . $addressProofFile->getClientName();
            if ($addressProofFile->move('uploads/seller/', $addressProof)) {
                $addressProof = 'uploads/seller/' . $addressProof;
                $data1 = [
                    'address_proof' => $addressProof,
                ];
                $sellerid =  $sellerModel->set($data1)->where('id', $this->request->getPost('seller_id'))->update();
            }
        } 

        // Handle national ID proof upload
        $nationalIdProof = '';
        $nationalIdProofFile = $this->request->getFile('national_id_proof');
        if ($nationalIdProofFile->isValid() && !$nationalIdProofFile->hasMoved()) {
            $nationalIdProof =  'national_id' . rand(10000000, 99999999) . time() . $nationalIdProofFile->getClientName();
            if ($nationalIdProofFile->move('uploads/seller/', $nationalIdProof)) {
                $nationalIdProof = 'uploads/seller/' . $nationalIdProof;
                $data2 = [
                    'national_id_proof' => $nationalIdProof
                ];
                $sellerid =  $sellerModel->set($data2)->where('id', $this->request->getPost('seller_id'))->update();
            } else {
                // Error moving file
                $errors = $nationalIdProofFile->getErrorString();
                $output['msg3'] = "Failed to move national ID proof: $errors";
            }
        } else {
            // Invalid file
            $output['msg33'] = "Invalid national ID proof";
        }

        $sellerData = [
            'email' => $this->request->getPost('email'),
            'store_name' => $this->request->getPost('store_name'),
            'mobile' => $this->request->getPost('mobile'),
            'city_id' => $this->request->getPost('city_id'),
            'deliverable_area_id' => $this->request->getPost('deliverable_area_id'),
            'pan_number' => $this->request->getPost('pan_number'),
            'tax_name' => $this->request->getPost('tax_name'),
            'tax_number' => $this->request->getPost('tax_number'),
            'require_products_approval' => $this->request->getPost('require_products_approval'),
            'view_customer_details' => $this->request->getPost('view_customer_details'),
            'commission' => $this->request->getPost('commission'),
            'store_address' => $this->request->getPost('store_address'),
            'account_number' => $this->request->getPost('account_number'),
            'account_name' => $this->request->getPost('account_name'),
            'bank_ifsc_code' => $this->request->getPost('bank_ifsc_code'),
            'bank_name' => $this->request->getPost('bank_name'),
            'branch' => $this->request->getPost('branch'),
            'name' => $this->request->getPost('name'),
            'map_address' => $this->request->getPost('map_address'),
            'latitude' => $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude'),
        ];

        $sellerid =  $sellerModel->set($sellerData)->where('id', $this->request->getPost('seller_id'))->update();

        if ($sellerid) {
            $data = [];
            foreach ($this->request->getPost('category') as $categoryId) {
                $data[] = [
                    'seller_id' => $this->request->getPost('seller_id'),
                    'category_id' => $categoryId
                ];
            }

            $sellerCategoryModel = new SellerCategoriesModel();
            $sellerCategoryModel->where('seller_id', $this->request->getPost('seller_id'))->delete();
            $sellerCategoryModel->insertBatch($data);

            $output['success'] = true;
            $output['msg'] = "Seller updated successfully";
        } else {
            $output['success'] = false;
            $output['msg'] = "Something went wrong";
        }


        return $this->response->setJSON($output);
    }

    public function deleteSellerCategory()
    {
        $output = ['success' => false, 'msg' => ''];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_edit('seller')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        
        $sellerCategoriesModel = new SellerCategoriesModel();
        $delete = $sellerCategoriesModel->where('seller_id', $this->request->getPost('seller_id'))->where('id', $this->request->getPost('seller_category_id'))->delete();

        if ($delete) {
            $output['success'] = true;
            $output['message'] = 'Seller category deleted successfully';
        } else {
            $output['message'] = 'Something went wrong';
        }
        return $this->response->setJSON($output);

    }
    public function transactionListView()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('seller-transaction')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $sellerModel = new SellerModel();
            $sellers = $sellerModel->select('id, name, store_name, mobile, balance')->where('is_delete', 0)->findAll();
            $countryModel = new CountryModel();
            $country = $countryModel->where('is_active', 1)->first();
            return view('seller/transactionList', [
                'settings' => $settingModel->getSettings(),
                'sellers' => $sellers,
                'country' => $country
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function transactionList()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_view('seller-transaction')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $sellerWalletTransactionModel = new SellerWalletTransactionModel();
        // Get input filters
        $txn_date = $this->request->getPost('txn_date');
        $seller = $this->request->getPost('seller');
        $method = $this->request->getPost('method');
        if (empty($txn_date)) {
            $today = date('Y-m-d');
            $txn_date = "$today - $today";
        }
        $dates = explode(' - ', $txn_date);

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        $builder = $sellerWalletTransactionModel->select(
            'seller_wallet_transaction.id, seller_wallet_transaction.seller_id, seller_wallet_transaction.order_id, seller_wallet_transaction.order_products_id, seller_wallet_transaction.type, seller_wallet_transaction.amount, seller_wallet_transaction.remark, seller_wallet_transaction.created_at,seller_wallet_transaction.updated_at, seller.name, seller.store_name, seller.mobile, seller.id as seller_id'
        )
            ->join('seller', 'seller.id = seller_wallet_transaction.seller_id', 'left');

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(seller_wallet_transaction.created_at) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(seller_wallet_transaction.created_at) <=', date('Y-m-d', strtotime($dates[1])));
        }
        if (!empty($seller)) {
            $builder->where('seller_wallet_transaction.seller_id', $seller);
        }
        if (!empty($method)) {
            $builder->where('seller_wallet_transaction.type', $method);
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
                $fundtransferList['remark'],
                $fundtransferList['created_at'],

            ];
        }
        return $this->response->setJSON($output);
    }
    public function addTransaction()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_add('seller-transaction')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        

        $sellerWalletTransactionModel = new SellerWalletTransactionModel();
        // Get input filters
        $seller_id = $this->request->getPost('seller_id');
        $type = $this->request->getPost('type');
        $amount = $this->request->getPost('amount');
        $remark = $this->request->getPost('remark');

        $sellerModel = new SellerModel();
        $seller = $sellerModel->where('is_delete', 0)->where('id', $seller_id)->first();
        $success = false;
        if ($type == 'debit') {
            if ($seller['balance'] >= $amount) {
                $new_closing_balance = $seller['balance'] - $amount;
                $data = [
                    'seller_id' => $seller_id,
                    'type' => $type,
                    'amount' => $amount,
                    'message' => $remark,
                    'created_at' => date("Y-m-d H:i:s")
                ];
                $success = $sellerWalletTransactionModel->insert($data);
            } else {
                $output = ['success' => false, "message" => "Amount should be equal or greater than balance."];
                return $this->response->setJSON($output);
            }
        }
        if ($type == 'credit') {
            $new_closing_balance = $seller['balance'] + $amount;
            $data = [
                'seller_id' => $seller_id,
                'type' => $type,
                'amount' => $amount,
                'message' => $remark,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $success = $sellerWalletTransactionModel->insert($data);
        }

        if ($success) {
            $sellerModel->set(['balance' => $new_closing_balance])->where('id', $seller_id)->update();

            $sellers = $sellerModel->select('id, name, store_name, balance')->where('is_delete', 0)->findAll();

            $countryModel = new CountryModel();
            $country = $countryModel->where('is_active', 1)->first();

            $output = ['success' => true, "message" => "Fund trasnfer done successfully", "seller" => $sellers, "currency" => $country['currency_symbol']];
        } else {
            $output = ['success' => false, "message" => "Something went wrong"];
        }


        return $this->response->setJSON($output);
    }
}
