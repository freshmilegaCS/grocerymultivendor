<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\BrandModel;
use App\Models\SettingsModel;

class Brand extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('brand')) {
                return redirect()->to('admin/permission-not-allowed');
            }

            $settingModel = new SettingsModel();
            
            $data['settings'] = $settingModel->getSettings();


            return view('brand/brand', $data);
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
        if (!can_view('brand')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        

        $brandModel = new BrandModel();
        $brands = $brandModel->orderBy('row_order')->findAll();
        $output['data'] = [];
        $x = 1;

        foreach ($brands as $row) {
            $img = "<a href='" . base_url($row['image']) . "' target='_blank'>
                        <img class='media-object round-media' src='" . base_url($row['image']) . "' alt='image' style='height: 75px; width: 40%'>
                    </a>";
            $action = "<a data-tooltip='tooltip' title='Edit Brand' href='" . base_url("admin/brand/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Brand' onclick='deletebrand(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";

            $output['data'][] = [
                $row['id'],
                $row['brand'],
                $img,
                $action,
            ];
            $x++;
        }

        return $this->response->setJSON($output);
    }
    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('brand')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $brandModel = new BrandModel();
            $brand = $brandModel->find($id);
            $settingModel = new SettingsModel();
            
            return view('brand/editBrand', [
                'settings' => $settingModel->getSettings(), 
                'brand' => $brand
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function add()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('brand')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $brand_name = $this->request->getPost('brand_name');
        $brand_image = $this->request->getPost('brand_image');

        // Validate and sanitize brand name
        $brand_name = filter_var($brand_name, FILTER_SANITIZE_STRING);
        $brand_name = str_replace("'", "’", $brand_name); // Replace single quotes with right single quote

        // Generate the initial slug
        $slug_prev = str_replace(" ", "-", $brand_name);
        $slug = preg_replace('/[^A-Za-z0-9-]/', '', strtolower($slug_prev));
        $slug1 = $slug;

        // Load the database model for posts
        $brandModel = new BrandModel();

        // Check if slug is available, add suffix if needed
        $check = true;
        $x = 1;
        while ($check) {
            $duplicateSlug = $brandModel->where('slug', $slug1)->countAllResults();

            if ($duplicateSlug > 0) {
                // If a duplicate slug is found, append a number to the slug
                $slug1 = $slug . $x;
            } else {
                // Slug is unique, break the loop
                $check = false;
            }
            $x++;
        }
        // Validate the image format
        if (strpos($brand_image, 'data:image') === 0) {
            list(, $brand_image) = explode(';', $brand_image);
            list(, $brand_image) = explode(',', $brand_image);
            $brand_image = base64_decode($brand_image);

            // Generate file path
            $db_file_path = 'uploads/brand/thump_' . time() . '.webp';
            $a_file_path = ROOTPATH . 'public_html/' . $db_file_path;

            // Write image to file
            if (file_put_contents($a_file_path, $brand_image) !== false) {
                // Insert brand into database
                $brandModel = new BrandModel();
                $data = [
                    'brand' => $brand_name,
                    'image'  => $db_file_path,
                    'slug'  => $slug1,
                ];

                if ($brandModel->insert($data)) {
                    $output['success'] = true;
                } else {
                    // Handle database insertion error
                    $output['message'] = 'Database error occurred.';
                }
            } else {
                // Handle file write error
                $output['message'] = 'Failed to save image.';
            }
        } else {
            // Handle invalid image data
            $output['message'] = 'Invalid image format.';
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
        if (!can_delete('brand')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $brand_id = $this->request->getPost('brand_id');

        $brandModel = new BrandModel();

        if ($brandModel->delete($brand_id)) {
            $output['success'] = true;
        } else {
            // Handle database insertion error
            $output['message'] = 'Database error occurred.';
        }



        return $this->response->setJSON($output);
    }

    public function update()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('brand')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $brand_name = $this->request->getPost('brand_name');
        $brand_id = $this->request->getPost('brand_id');
        $brand_image = $this->request->getPost('files');

        // Validate and sanitize brand name
        $brand_name = filter_var($brand_name, FILTER_SANITIZE_STRING);

        if ($brand_image == "") {
            $data = [
                'brand' => $brand_name
            ];
        } else {
            list(, $brand_image) = explode(';', $brand_image);
            list(, $brand_image) = explode(',', $brand_image);
            $brand_image = base64_decode($brand_image);

            // Generate file path
            $db_file_path = 'uploads/brand/thump_' . time() . '.webp';
            $a_file_path = ROOTPATH . 'public_html/' . $db_file_path;
            file_put_contents($a_file_path, $brand_image);
            $data = [
                'brand' => $brand_name,
                'image'  => $db_file_path
            ];
        }

        $brandModel = new BrandModel();


        if ($brandModel->where('id', $brand_id)->set($data)->update()) {
            $output['success'] = true;
        } else {
            $output['message'] = 'Database error occurred.';
        }

        return $this->response->setJSON($output);
    }
    public function brandOrder()
    {
        $session = session();

        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('brand')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $brandModel = new BrandModel();
            $brands = $brandModel->orderBy('row_order')->findAll();
            $settingModel = new SettingsModel();
            
            return view('permissionNotAllowed', [
                'settings' => $settingModel->getSettings(), 
                'brands' => $brands
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function brandOrderUpdate()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('brand')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $request = $this->request->getJSON();
        $order = $request->order;

        $brand_model = new BrandModel();

        foreach ($order as $index => $productId) {
            // Update the row_order field in the database based on the new order
            $brand_model->update($productId, ['row_order' => $index + 1]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Brand order updated']);
    }
}
