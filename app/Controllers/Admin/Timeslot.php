<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\SettingsModel;
use App\Models\TimeslotModel;

class Timeslot extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('timeslot')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('timeslot', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function list()
    {
        $timeslotModel = new TimeslotModel();

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('timeslot')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Fetch data from the model
        $timeslots = $timeslotModel->getAllTimeslots();

        // Prepare data for JSON output
        $out = array('data' => array());
        foreach ($timeslots as $row) {
            $btn = "            <a type='button' data-tooltip='tooltip' title='Delete Timeslot' onclick='deletetime(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'></i></a>";
            $out['data'][] = array(
                $row['id'],
                $row['mintime'],
                $row['maxtime'],
                $btn
            );
        }

        // Return JSON response
        return $this->response->setJSON($out);
    }

    // Method for deleting timeslots
    public function delete()
    {
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('timeslot')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output['success'] = false;

        if ($this->request->getPost('id')) {
            $id = $this->request->getPost('id');
            $timeslotModel = new TimeslotModel();
            if ($timeslotModel->delete($id)) {
                $output['success'] = true;
                $output['message'] = "TimeSlot deleted successfully!";
            } else {
                $output['message'] = "Something went wrong";
            }
        }
        return $this->response->setJSON($output);
    }
    public function add()
    {
        $response = ['success' => false];
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('timeslot')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Validate and sanitize inputs
        $minTime = $this->request->getPost('min_time');
        $maxTime = $this->request->getPost('max_time');

        // Convert to 'H:i' format
        $mintime = date('H:i', strtotime($minTime));
        $maxtime = date('H:i', strtotime($maxTime));

        $timeslotModel = new TimeslotModel();

        // Check if timeslot exists
        if (!$timeslotModel->timeslotExists($mintime, $maxtime)) {
            // Insert the timeslot
            if ($timeslotModel->insertTimeslot($mintime, $maxtime)) {
                $response['success'] = true;
            }
        }

        // Return JSON response
        return $this->response->setJSON($response);
    }

    public function changeTimeslotStatus()
    {
        $timeslotModel = new TimeslotModel();

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        $timeslot_id = $this->request->getPost('id');

        $timeslot = $timeslotModel->where('id', $timeslot_id)->first();

        if ($timeslot['status'] == 1) {
            $newStatus = 0;
        } else {
            $newStatus = 1;
        }

        // Update the status in the database
        $updateResult = $timeslotModel->update($timeslot_id, ['status' => $newStatus]);

        if ($updateResult) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Timeslot successfully",
                'new_status' => $newStatus
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update timeslot status'
            ]);
        }
    }
}
