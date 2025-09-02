<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\CityModel;

use App\Models\SettingsModel;
use App\Models\OrderModel;

class ManageCity extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('city')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $data['countrySetting'] = $this->country;
            $data['timeZoneSetting'] = $this->timeZone;
            
            return view('city/manageCity', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function get_city_list()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            $cityModel = new cityModel();
            $city = $cityModel->getAllCity();
            return $this->response->setJSON(['data' => $city]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function edit($id)
    {
        $settingModel = new SettingsModel();
        $data['settings'] = $settingModel->getSettings();
        $data['countrySetting'] = $this->country;
        $data['timeZoneSetting'] = $this->timeZone;
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('city')) {
            return redirect()->to('admin/permission-not-allowed');
        }

        $cityModel = new cityModel();
        $data['city'] = $cityModel->where('id', $id)->first();

        $orderModel = new OrderModel();
        $data['pendingOrders'] = $orderModel->getOrdersByStatus(1);
        return view('city/editManageCity', $data);
    }

    public function addCity()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('city')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $longitude = $this->request->getPost('longitude');
        $latitude = $this->request->getPost('latitude');
        $city_name = $this->request->getPost('city_name');

        $CityModel = new CityModel();

        $success = $CityModel->addCity(
            $longitude,
            $latitude,
            $city_name,
        );

        // Prepare the response
        $response = [
            'success' => $success,
        ];

        if (!$success) {
            $response['error'] = 'Failed to add city. Please try again.';
        } else {
            $response = [
                'success' => true,
                'message' => 'City added successfully!',
            ];
        }

        // Send JSON response
        return $this->response->setJSON($response);
    }

    public function deleteCity()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('city')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $city_id = $this->request->getPost('city_id');

        $CityModel = new CityModel();

        $success = $CityModel->deleteCity(
            $city_id,
        );

        // Prepare the response
        if ($success) {
            // Record deleted successfully
            return $this->response->setJSON(['success' => true, 'message' => 'City deleted successfully']);
        } else {
            // Failed to delete record
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete city']);
        }
    }
    public function updateCity()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }


        if (!can_edit('city')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        
        $city_id = $this->request->getPost('editid');
        $longitude = $this->request->getPost('longitude');
        $latitude = $this->request->getPost('latitude');
        $city_name = $this->request->getPost('city_name');

        $CityModel = new CityModel();

        $success = $CityModel->updateCity(
            $city_id,
            $longitude,
            $latitude,
            $city_name,
        );


        // Prepare the response
        if ($success) {
            return $this->response->setJSON(['success' => true, 'message' => 'City updated successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to update city']);
        }
    }
}
