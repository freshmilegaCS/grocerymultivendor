<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\HighlightsModel;
use App\Models\SellerModel;
use App\Models\SettingsModel;


class Highlights extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_add('highlights')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $sellerModel = new SellerModel();
            $data['sellers'] = $sellerModel->where('status', 1)->where('is_delete', 0)->findAll();
            return view('highlights/highlights', $data);
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
        if (!can_view('highlights')) {
            return redirect()->to('admin/permission-not-allowed');
        }

        $highlightModel = new HighlightsModel();
        $highlights = $highlightModel->getAllHighlights();

        // Prepare the output array
        $output['data'] = [];

        foreach ($highlights as $row) {
            // Media display
            $media_display = '';
            if ($row['image'] != '') {
                $media_display = "<img src='" . base_url($row['image']) . "' alt='Highlight Image' width='50' height='50'>";
            } else {
                $media_display = "<a href='https://youtu.be/{$row['video']}' target='_blank' class='btn btn-info btn-xs'>View Video</a>";
            }

            // Action buttons
            $action = "
                <a data-tooltip='tooltip' title='Edit Highlight' href='" . base_url("admin/highlight/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                    <i class='fi fi-tr-customize-edit'></i>
                </a> 
                <a type='button' data-tooltip='tooltip' title='Delete Highlight' onclick='deletehighlights(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'>
                    <i class='fi fi-tr-trash-xmark'></i>
                </a>";

            // Add row data
            $output['data'][] = [
                $row['id'],
                esc($row['title']),
                esc($row['description']),
                $media_display,
                $action
            ];
        }

        // Return the output as JSON
        return $this->response->setJSON($output);
    }

    public function delete()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('highlights')) {
            return redirect()->to('admin/permission-not-allowed');
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output['success'] = false;

        $highlight_id = $this->request->getPost('highlight_id');

        $highlightModel = new HighlightsModel();
        $deleteFaq = $highlightModel->deleteHighlight($highlight_id);
        if ($deleteFaq) {
            $output['success'] = true;
            $output['message'] = 'Highlight deleted successfully';
        } else {
            $output['message'] = 'Unable to delete Highlight';
        }
        return $this->response->setJSON($output);
    }

    public function add()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('highlights')) {
            return redirect()->to('admin/permission-not-allowed');
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output = ['success' => false];

        $seller_id = $this->request->getPost('seller_id');
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $media_type = $this->request->getPost('media_type');
        $video = $this->request->getPost('video');

        // Validate required fields
        if (!$seller_id || !$title || !$description  || !$media_type) {
            $output['message'] = 'All fields are required';
            return $this->response->setJSON($output);
        }

        // Handle file upload if media type is image
        $db_file_path = '';
        if ($media_type === 'image') {
            if ($files = $this->request->getFiles()) {
                if (isset($files['image']) && is_array($files['image'])) {
                    foreach ($files['image'] as $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            $newName = $file->getRandomName();
                            $file->move('uploads/highlight/', $newName);
                            $db_file_path = 'uploads/highlight/' . $newName;
                        }
                    }
                }
            }

            $data = [
                "seller_id"   => $seller_id,
                "title"       => $title,
                "description" => $description,
                "image" => $db_file_path
            ];
        } else {
            $videoid = $this->getYoutubeVideoId($video);
            $data = [
                "seller_id"   => $seller_id,
                "title"       => $title,
                "description" => $description,
                "video" => $videoid
            ];
        }

        // Prepare data for insertion
        $highlightModel = new HighlightsModel();


        // Insert data into the database
        $insertHighlight = $highlightModel->addHighlight($data);
        if ($insertHighlight) {
            $output['success'] = true;
            $output['message'] = 'Highlight added successfully';
        } else {
            $output['message'] = 'Unable to add Highlight';
        }
        return $this->response->setJSON($output);
    }
    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('highlights')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $highlightModel = new HighlightsModel();
            $data['highlights'] = $highlightModel->getHighlightById($id);
            $sellerModel = new SellerModel();
            $data['sellers'] = $sellerModel->where('status', 1)->where('is_delete', 0)->findAll();
            return view('highlights/editHighlights', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function update()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('highlights')) {
            return redirect()->to('admin/permission-not-allowed');
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $output = ['success' => false];

        $highlights_id = $this->request->getPost('highlights_id');
        $seller_id = $this->request->getPost('seller_id');
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $media_type = $this->request->getPost('media_type');
        $video = $this->request->getPost('video');

        // Validate required fields
        if (!$seller_id || !$title || !$description  || !$media_type) {
            $output['message'] = 'All fields are required';
            return $this->response->setJSON($output);
        }

        // Handle file upload if media type is image
        $db_file_path = '';
        if ($media_type === 'image') {
            if ($files = $this->request->getFiles()) {
                if (isset($files['image']) && is_array($files['image'])) {
                    foreach ($files['image'] as $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            $newName = $file->getRandomName();
                            $file->move('uploads/highlight/', $newName);
                            $db_file_path = 'uploads/highlight/' . $newName;
                        }
                    }
                    $data = [
                        "seller_id"   => $seller_id,
                        "title"       => $title,
                        "description" => $description,
                        "image" => $db_file_path,
                        "video" => ""
                    ];
                } else {
                    $data = [
                        "seller_id"   => $seller_id,
                        "title"       => $title,
                        "description" => $description,
                        "video" => ""

                    ];
                }
            } else {
                $data = [
                    "seller_id"   => $seller_id,
                    "title"       => $title,
                    "description" => $description,
                    "video" => ""

                ];
            }
        } else {
            $videoid = $this->getYoutubeVideoId($video);
            $data = [
                "seller_id"   => $seller_id,
                "title"       => $title,
                "description" => $description,
                "video" => $videoid,
                "image" => ""
            ];
        }

        // Prepare data for insertion
        $highlightModel = new HighlightsModel();


        // Insert data into the database
        $insertHighlight = $highlightModel->updateHighlight($highlights_id, $data);
        if ($insertHighlight) {
            $output['success'] = true;
            $output['message'] = 'Highlight updated successfully';
        } else {
            $output['message'] = 'Unable to update Highlight';
        }
        return $this->response->setJSON($output);
    }

    private function getYoutubeVideoId($url)
    {
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)|.*[?&]v=)|youtu\.be/)([a-zA-Z0-9_-]{11})%';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }
}
