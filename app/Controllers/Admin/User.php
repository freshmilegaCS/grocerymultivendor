<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\UserModel;
use App\Models\WalletModel;
use App\Models\AdminModel;
use App\Models\CountryModel;
use App\Models\DeviceTokenModel;
use App\Models\SettingsModel;

class User extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('manage-user')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            return view('/user/user', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function list()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('manage-user')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $userModel = new UserModel();
        $users = $userModel->getUsersWithWallet();

        $output['data'] = [];
        $x = 1;
        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();
        foreach ($users as $user) {

            if (isset($user['closing_amount'])) {
                $wallet = '<a title="Wallet History" class="btn btn-link" onclick="walletHistoryModel(' . $user['id'] . ')">' . $country['currency_symbol'] . ' ' . $user['closing_amount'] . '</a>';
            } else {
                $wallet = '<a title="Wallet History" class="btn btn-link" onclick="walletHistoryModel(' . $user['id'] . ')">' . $country['currency_symbol'] . ' 0</a>';
            }

            $wallet .= "<a type='button' data-tooltip='tooltip' title='Add Wallet Amount' onclick='addWalletAmount(" . $user['id'] . ")' class='btn btn-secondary-light  btn-xs'><i class='fi fi-tr-wallet'></i></a>";
            $status = $user['is_active'] == 1
                ? "<span class='badge badge-success'>Active</span>"
                : "<span class='badge badge-danger'>Inactive</span>";

            $mailto = "<a class='text-dark' href='mailto:" . $user['email'] . "'>" . $user['email'] . "</a>";
            $tel = "<a class='text-dark' href='tel:" . $user['mobile'] . "'>" . $user['mobile'] . "</a>";
            $action = "
                <a type='button' data-tooltip='tooltip' title='Change Status' onclick='editstatus(" . $user['id'] . ", " . $user['is_active'] . ")' class='btn btn-primary-light  btn-xs' data-toggle='modal' data-target='#editStudentModal'><i class='fi fi-tr-customize-edit'></i></a>
                <a type='button' data-tooltip='tooltip' title='Delete User' onclick='deleteuser(" . $user['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'></i></a>";

            $output['data'][] = [
                $x,
                $user['name'],
                $mailto . "<br>" . $tel,
                date("h:i A, dS M Y", strtotime($user['created_at'])),
                $status,
                $user['ref_code'],
                $wallet,
                $action
            ];
            $x++;
        }


        return $this->response->setJSON($output);
    }

    public function get_search_user()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('manage-user')) {
                $output =  '<li class="list-group-item" style="cursor: pointer;" > Permission not allowed</li>';
                return $this->response->setBody($output);
            }
            $name = $this->request->getPost('name');

            $userModel = new UserModel();
            $users = $userModel->searchUserByNameOrEmailOrMobile($name);

            $output = '';
            if (!empty($users)) {
                foreach ($users as $user) {
                    $output .= '<li class="list-group-item" style="cursor: pointer;" onclick="selectUser(' . $user['id'] . ', \'' . $user['name'] . ' \',  \'' . $user['email'] . '\')">' . $user['name'] . '  (' . $user['email'] . ')</li>';
                }
            } else {
                $output .= '<li class="list-group-item" onclick="listnotfound()"> <i class="fa fa-exclamation-triangle "></i> User not found try another keyword</li>';
            }

            return $this->response->setBody($output);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function getUserWallet()
    {
        $response = ['data' => []];

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_view('manage-user-wallet')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $userId = $this->request->getPost('user_id');

        // Load the models
        $userModel = new UserModel();
        $walletModel = new WalletModel();

        // Fetch the user details
        $user = $userModel->getUserById($userId);

        // Fetch the latest wallet amount
        $wallet = $walletModel->getLatestWalletAmount($userId);

        if ($user) {
            $closingAmount = isset($wallet['closing_amount']) ? $wallet['closing_amount'] : 0;

            $response['data'][] = [
                'name' => $user['name'],
                'closing_amount' => '₹ ' . $closingAmount
            ];
        }

        // Return the response as JSON
        return $this->response->setJSON($response);
    }

    public function addAmountById()
    {
        $output['success'] = false;
        $output['message'] = 'Something went wrong';

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login');
        }

        if (!can_add('manage-user-wallet')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if (!$this->request->getPost(['user_id', 'walletAmount', 'flag', 'remark'])) {
            return $this->response->setJSON($output);
        }

        helper('firebase_helper');

        $user_id = (int) $this->request->getPost('user_id');
        $walletAmount = (int) $this->request->getPost('walletAmount');
        $flag = $this->request->getPost('flag', FILTER_SANITIZE_STRING);
        $remark = $this->request->getPost('remark', FILTER_SANITIZE_STRING);

        $userModel = new UserModel();
        $walletModel = new WalletModel();
        $actualWalletAmount = $walletModel->calculateActualWalletAmount($user_id, $walletAmount, $flag);

        if (isset($actualWalletAmount['error'])) {
            $output['message'] = $actualWalletAmount['error'];
            return $this->response->setJSON($output);
        }

        if ($user_id <= 0) {
            $output['message'] = 'User not found';
            return $this->response->setJSON($output);
        }

        $result = $walletModel->insertWalletTransaction($user_id, $walletAmount, $actualWalletAmount, $flag, $remark);

        if (!$result) {
            return $this->response->setJSON($output);
        }

        $deviceTokenModel = new DeviceTokenModel();
        $userToken = $deviceTokenModel->getDeviceTokens(['user_type' => 2, 'user_id' => $user_id]);

        if ($userToken) {
            $countryModel = new CountryModel();
            $country = $countryModel->where('is_active', 1)->first();
            $dataForNotification = ['screen' => 'Notification'];
            sendFirebaseNotification($userToken['app_key'], 'Wallet Updated', $country['currency_symbol'] . $actualWalletAmount . " " . $flag . " - " . $remark, $dataForNotification);
        }

        $userModel->set(['wallet' => $actualWalletAmount])->update($user_id);

        $output['success'] = true;
        $output['message'] = 'Wallet updated successfully';
        return $this->response->setJSON($output);
    }


    public function changePass()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();


            return view('/profile/profile', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function resetPassword()
    {
        $session = session();
        if ($this->request->isAJAX()) {
            $rules = [
                'current_password' => 'required|min_length[6]',
                'password' => 'required|min_length[6]',
                'confirm_password' => 'required|matches[password]',
            ];

            if (!$this->validate($rules)) {
                $response = [
                    'success' => false,
                    'message' => $this->validator->getErrors()
                ];
            } else {
                $currentPassword = $this->request->getPost('current_password');
                $password = $this->request->getPost('password');

                if ($session->has('user_id') && session('account_type') == 'Admin') {
                    $AdminModel = new AdminModel();
                    $user = $AdminModel->where('id', $session->get('user_id'))->first();

                    if (password_verify($currentPassword, $user['password'])) {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $AdminModel->update($user['id'], ['password' => $hashedPassword]);

                        $response = [
                            'success' => true,
                            'message' => 'Password updated successfully.'
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Current password is incorrect.'
                        ];
                    }
                }
            }

            // Return JSON response
            return $this->response->setJSON($response);
        }
    }

    public function userWalletList()
    {
        $user_id = $this->request->getPost('id');
        if (!can_view('manage-user-wallet')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $walletModel = new WalletModel();
        $wallet = $walletModel->where('user_id', $user_id)->orderBy('id', 'DESC')->findAll();
        $output['data'] = [];

        foreach ($wallet as $walletRow) {
            $amt = "";
            $flag = "";
            if ($walletRow['flag'] == 'top_up' || $walletRow['flag'] == 'fund_return') {
                $amt = "<h6 class='text-success'>+ " . $walletRow['amount'] . "</h6>";
                $flag = "<h6 class='text-success'>" . $walletRow['flag'] . "</h6>";
            } elseif ($walletRow['flag'] == 'debit' || $walletRow['flag'] == 'purchase') {
                $amt = "<h6 class='text-danger'>- " . $walletRow['amount'] . "</h6>";
                $flag = "<h6 class='text-danger'>" . $walletRow['flag'] . "</h6>";
            }

            $refBy = '';
            if ($walletRow['ref_user_id'] != 0) {
                $user = $walletModel->where('id', $walletRow['ref_user_id'])->first();
                if ($user) {
                    $refBy = 'by ' . $user['mobile'] . "<br/>" . $user['name'];
                }
            }

            $output['data'][] = [
                $amt,
                $flag,
                $walletRow['remark'] . "<br/>" . $refBy,
                date("h:i A, dS M Y", strtotime($walletRow['date']))
            ];
        }

        return $this->response->setJSON($output);
    }
    
    public function updateStatus()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('manage-user')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $userId = $this->request->getPost('userid');
            $status = $this->request->getPost('status');
            // Load the models
            $userModel = new UserModel();

            $data = ["is_active" => $status == 1 ? 0 : 1];
            $user = $userModel->set($data)->where('id', $userId)->update();
            $response = [
                'success' => true,
                'message' => 'Status updated successfully'
            ];

            // Return the response as JSON
            return $this->response->setJSON($response);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function delete()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_delete('manage-user')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $userId = $this->request->getPost('user_id');
            // Load the models
            $userModel = new UserModel();

            $data = ["is_delete" => 1, "is_active" => 0 ];
            $user = $userModel->set($data)->where('id', $userId)->update();
            $response = [
                'success' => true,
                'message' => 'User deleted successfully'
            ];

            // Return the response as JSON
            return $this->response->setJSON($response);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
}
