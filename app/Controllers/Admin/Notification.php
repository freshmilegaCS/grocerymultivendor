<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\DeviceTokenModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class Notification extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('notification')) {
                return redirect()->to('admin/permission-not-allowed');
            }

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('/notification/notification', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function add()
    {
        date_default_timezone_set($this->timeZone['timezone']);
        helper('firebase_helper');
        $success = false;
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('notification')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        

        $notificationModel = new NotificationModel();
        $userModel = new UserModel();


        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $user_type = $this->request->getPost('user_type');



        if ($user_type == 0) {
            $users = $userModel->where('is_active', 1)->where('is_delete', 0)->findAll();
            $userId = 0;
        } else {
            $userId = $this->request->getPost('user_id');
            $users = $userModel->where('id', $userId)->where('is_active', 1)->where('is_delete', 0)->findAll();
        }

        $data = [
            'user_id' => $userId,
            'title' => $title,
            'msg' => $description,
            'date' => date('Y-m-d H:i:s'),
            'is_system_generated' => 0
        ];
        $deviceTokenModel = new DeviceTokenModel();
        foreach ($users as $user) {

            $dataForNotification = [
                'screen' => 'Notification',
            ];
            $deviceToken = $deviceTokenModel->where('user_id', $user['id'])->orderBy('id', 'desc')->first();
            if (isset($deviceToken['app_key'])) {
                sendFirebaseNotification($deviceToken['app_key'], $title, $description, $dataForNotification);
            }
        }


        $success = $notificationModel->insert($data);



        // Handle success or failure
        if ($success) {
            // Record deleted successfully
            return $this->response->setJSON(['success' => true, 'message' => 'Notification send successfully']);
        } else {
            // Failed to delete record
            return $this->response->setJSON(['success' => false, 'message' => 'something went wrong']);
        }
    }

    public function list()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('notification')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $notificationModel = new NotificationModel();
        $is_system_generated = $this->request->getPost('is_system_generated');

        $notifications = $notificationModel->getNotifications($is_system_generated);

        $output = [
            'data' => []
        ];

        $x = 1;
        foreach ($notifications as $row) {
            if ($row['user_id'] > 0) {
                $userModel = new UserModel();
                $user = $userModel->find($row['user_id']);
                $user_name = $user ? $user['name'] : '';
            } else {
                $user_name = "All Users";
            }

            $action = "<a type='button' data-tooltip='tooltip' title='Delete Notification' onclick='deleteNotification(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'></i></a>";
            $date = date("d-M-Y H:i:s", strtotime($row['date']));

            $output['data'][] = [
                $x,
                $user_name,
                $row['title'],
                $row['msg'],
                $date,
                $action
            ];

            $x++;
        }

        echo json_encode($output);
    }
    public function delete()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('notification')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $noti_id = $this->request->getPost('noti_id');
        $NotificationModel = new NotificationModel();

        $success = $NotificationModel->deleteNotification(
            $noti_id,
        );

        // Prepare the response
        if ($success) {
            // Record deleted successfully
            return $this->response->setJSON(['success' => true, 'message' => 'Notification deleted successfully']);
        } else {
            // Failed to delete record
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete service']);
        }
    }
    public function tokenUpdate()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        $token = $this->request->getPost('token');
        $deviceTokenModel = new DeviceTokenModel();
        $data = [
            'user_type' => 1,  //for admin
            'user_id' => session()->get('user_id'),
            'app_key' => $token
        ];

        $query = $deviceTokenModel
            ->where('user_type', 1)
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
