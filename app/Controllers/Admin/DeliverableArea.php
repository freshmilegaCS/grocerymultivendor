<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\CityModel;

use App\Models\DeliverableAreaModel;
use App\Helpers\CommonHelper;
use App\Models\OrderModel;
use App\Models\SettingsModel;
use CodeIgniter\API\ResponseTrait;

class DeliverableArea extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_add('deliverable_area')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $data['countrySetting'] = $this->country;
            $data['timeZoneSetting'] = $this->timeZone;
            $cityModel = new cityModel();
            $data['city'] = $cityModel->getAllCity();

            return view('deliverableArea/deliverableArea', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function view()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('deliverable_area')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $data['countrySetting'] = $this->country;
            $data['timeZoneSetting'] = $this->timeZone;

            return view('deliverableArea/deliverableAreaList', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function list()
    {
        $DeliverableAreaModel = new DeliverableAreaModel();
        $deliverableAreas = $DeliverableAreaModel->view();
        if (!can_view('deliverable_area')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $data = [];
        foreach ($deliverableAreas as $deliverableArea) {
            $cityName = $this->getCityName($deliverableArea['city_id']);

            $action = '<a data-tooltip="tooltip" title="Edit Deliverable Area" href="edit/' . $deliverableArea['id'] . '" class="btn btn-primary-light btn-xs"><i class="fi fi-tr-customize-edit"></i></a> <button type="button" data-tooltip="tooltip" title="Delete Deliverable Area" onclick="deleteDeliverableArea(' . $deliverableArea['id'] . ')" class="btn btn-danger-light btn-xs"><i class="fi fi-tr-trash-xmark"></i></button>';

            // $action .= " <a type='button' data-tooltip='tooltip' title='Add Delivery Date' onclick='addDeliveryDate(" . $deliverableArea['id'] . ")' class='btn btn-warning-light  btn-xs'><i class='fi fi-tr-calendar-days'></i></a>";

            // $action .= " <a type='button' data-tooltip='tooltip' title='Add Timeslot' onclick='addTimeslot(" . $deliverableArea['id'] . ")' class='btn btn-secondary-light  btn-xs'><i class='fi fi-tr-duration-alt'></i></a>";

            $data[] = [
                $deliverableArea['id'],
                $cityName,
                $deliverableArea['deliverable_area_title'],
                $action
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    private function getCityName($cityId)
    {
        $cityModel = new CityModel();
        $city = $cityModel->find($cityId);

        return $city ? $city['name'] : '';
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
        if (!can_edit('deliverable_area')) {
            return redirect()->to('admin/permission-not-allowed');
        }

        $DeliverableAreaModel = new DeliverableAreaModel();
        $data['deliverable_area'] = $DeliverableAreaModel->select('deliverable_area.*, city.latitude, city.longitude')->join('city', 'city.id = deliverable_area.city_id')->where('deliverable_area.id', $id)->first();

        $cityModel = new cityModel();
        $data['city'] = $cityModel->getAllCity();

        $orderModel = new OrderModel();
        $data['pendingOrders'] = $orderModel->getOrdersByStatus(1);

        return view('deliverableArea/editDeliverableArea', $data);
    }

    public function add()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('deliverable_area')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $boundary_points = $this->request->getPost('boundary_points');
        $boundary_points_web = $this->request->getPost('boundary_points_web');
        $edit_city = $this->request->getPost('edit_city');
        $radius = $this->request->getPost('radius');
        $geolocation_type = $this->request->getPost('geolocation_type');
        $deliverable_area = $this->request->getPost('deliverable_area');
        $time_to_travel = $this->request->getPost('time_to_travel');
        $min_amount_for_free_delivery = $this->request->getPost('min_amount_for_free_delivery');
        $delivery_charge_method = $this->request->getPost('delivery_charge_method');
        $delivery_charge = $this->request->getPost('delivery_charge');
        $base_delivery_time = $this->request->getPost('base_delivery_time');

        $DeliverableAreaModel = new DeliverableAreaModel();

        $success = $DeliverableAreaModel->add(
            $boundary_points,
            $boundary_points_web,
            $edit_city,
            $radius,
            $geolocation_type,
            $deliverable_area,
            $time_to_travel,
            $min_amount_for_free_delivery,
            $delivery_charge_method,
            $delivery_charge,
            $base_delivery_time

        );

        // Prepare the response
        $response = [
            'success' => $success,
        ];

        if (!$success) {
            $response['error'] = 'Failed to add Area. Please try again.';
        } else {
            $response = [
                'success' => true,
                'message' => 'Area added successfully!',
            ];
        }

        // Send JSON response
        return $this->response->setJSON($response);
    }
    public function update()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('deliverable_area')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $boundary_points = $this->request->getPost('boundary_points');
        $boundary_points_web = $this->request->getPost('boundary_points_web');
        $edit_city = $this->request->getPost('edit_city');
        $radius = $this->request->getPost('radius');
        $geolocation_type = $this->request->getPost('geolocation_type');
        $deliverable_area = $this->request->getPost('deliverable_area');
        $time_to_travel = $this->request->getPost('time_to_travel');
        $min_amount_for_free_delivery = $this->request->getPost('min_amount_for_free_delivery');
        $delivery_charge_method = $this->request->getPost('delivery_charge_method');
        $delivery_charge = $this->request->getPost('delivery_charge');
        $edit_id = $this->request->getPost('edit_id');
        $base_delivery_time = $this->request->getPost('base_delivery_time');


        $DeliverableAreaModel = new DeliverableAreaModel();
        $data = [
            'boundry_points' => $boundary_points,
            'boundary_points_web' => $boundary_points_web,
            'city_id' => $edit_city,
            'radius' => $radius,
            'geolocation_type' => $geolocation_type,
            'deliverable_area_title' => $deliverable_area,
            'time_to_travel' => $time_to_travel,
            'min_amount_for_free_delivery' => $min_amount_for_free_delivery,
            'delivery_charge_method' => $delivery_charge_method,
            $delivery_charge_method => $delivery_charge,
            'base_delivery_time' => $base_delivery_time

        ];
        $success = $DeliverableAreaModel->set($data)->where('id', $edit_id)->update();


        // Prepare the response
        $response = [
            'success' => $success,
        ];

        if (!$success) {
            $response['error'] = 'Failed to update Area. Please try again.';
        } else {
            $response = [
                'success' => true,
                'message' => 'Area updated successfully!',
            ];
        }

        // Send JSON response
        return $this->response->setJSON($response);
    }
    public function getByCityId()
    {
        $city_id = $this->request->getPost('city_id');

        $deliverableAreaModel = new DeliverableAreaModel();
        $areas = $deliverableAreaModel->getDeliverableAreasByCity($city_id);

        return $this->respond($areas);
    }
    public function delete()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('deliverable_area')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $deliverable_area_id = $this->request->getPost('deliverable_area_id');

        $DeliverableAreaModel = new DeliverableAreaModel();
        $data = [
            'is_delete' => 1
        ];
        $success = $DeliverableAreaModel->set($data)
            ->where('id', $deliverable_area_id)
            ->update();

        // Prepare the response
        if ($success) {
            // Record deleted successfully
            return $this->response->setJSON(['success' => true, 'message' => 'Deliverable area deleted successfully']);
        } else {
            // Failed to delete record
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete deliverable area']);
        }
    }


}
