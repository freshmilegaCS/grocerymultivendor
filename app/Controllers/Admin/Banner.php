<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\BannerModel;
use App\Models\CategoryModel;
use App\Models\SettingsModel;

class Banner extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('banner')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->getCategories();

            return view('/banner/add', [
                'settings' => $settingModel->getSettings(),

                'categories' => $categories
            ]);
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
        if (!can_view('banner')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $bannerModel = new BannerModel();
        $banners = $bannerModel->getActiveBanners();

        $output = ['data' => []];
        foreach ($banners as $banner) {
            $img = "<a href='" . base_url($banner['image']) . "' target='_blank'><img class='media-object round-media' src='" . base_url($banner['image']) . "' alt='image' style='height: 75px;width:40%'></a>";
            $action = "<a data-tooltip='tooltip' title='Edit Banner' href='" . base_url('/admin/banner/edit/' . $banner['id']) . "' class='btn btn-primary-light  btn-xs'><i class='fi fi-tr-customize-edit'></i></a>  
                       <a type='button' data-tooltip='tooltip' title='Delete Banner' onclick='deletebanner({$banner['id']})' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'></i></a>";
            if ($banner['status'] == 0) {
                $bannerType = '<span class="badge badge-success">Header</span>';
            } else  if ($banner['status'] == 1) {
                $bannerType = '<span class="badge badge-warning">Deal of the day</span>';
            } else  if ($banner['status'] == 2) {
                $bannerType = '<span class="badge badge-primary">Home section</span>';
            } else  if ($banner['status'] == 3) {
                $bannerType = '<span class="badge badge-danger">Footer</span>';
            }
            $output['data'][] = [
                $banner['number'],
                $banner['category'] . "<br>" . $bannerType,
                $img,
                $action
            ];
        }

        return $this->response->setJSON($output);
    }
    public function add()
    {
        $output = ['success' => false];

        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('banner')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }


        // Validate the input (you can adjust validation rules as needed)
        $rules = [
            'banner_type' => 'required|integer',
            'banner_img' => 'required',
        ];

        if ($this->validate($rules)) {
            $categoryId = $this->request->getPost('category_id');
            $bannerType = $this->request->getPost('banner_type');


            $bannerImg = $this->request->getPost('banner_img');
            list(, $bannerImg) = explode(';', $bannerImg);
            list(, $bannerImg) = explode(',', $bannerImg);
            $bannerImg = base64_decode($bannerImg);

            // Use FCPATH for the public directory
            $db_file_path = 'uploads/banner/banner_' . time() . '.webp';
            $full_file_path = FCPATH . $db_file_path;

            // Create directory if it doesn't exist
            if (!is_dir(dirname($full_file_path))) {
                mkdir(dirname($full_file_path), 0777, true);
            }

            // Save the image to the file system
            if (file_put_contents($full_file_path, $bannerImg)) {
                // Prepare data for insertion
                $bannerModel = new BannerModel();
                $data = [
                    'banner_img' => $db_file_path,
                    'category_id' => $categoryId == "" ? 0 : $categoryId,
                    'status' => $bannerType
                ];

                // Insert banner into database
                if ($bannerModel->insertBanner($data)) {
                    $output['success'] = true;
                    $output['message'] = "Banner added ";
                } else {
                    $output['message'] = "Unable to add banner";
                }
            }
        } else {
            $output['message'] = "Entered data is not in correct format";
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
        if (!can_delete('banner')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Check if banner ID is provided
        $bannerId = $this->request->getPost('ban_id');

        if ($bannerId) {
            $bannerModel = new BannerModel();

            // Attempt to delete the banner
            if ($bannerModel->deleteBanner($bannerId)) {
                $output['success'] = true;
            }
        }

        return $this->response->setJSON($output);
    }

    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('banner')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $bannerModel = new BannerModel();
            $banner = $bannerModel->find($id);
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->getCategories();

            return view('banner/edit', [
                'settings' => $settingModel->getSettings(),

                'banner' => $banner,
                'categories' => $categories

            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function update()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('banner')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        // Get POST data
        $category_id = $this->request->getPost('category_id');
        $banner_id = $this->request->getPost('banner_id');
        $banner_type = $this->request->getPost('banner_type');
        $banner_img = $this->request->getPost('banner_img');


        if ($banner_img == "") {
            $data = [
                'category_id' => $category_id ?? 0,
                'status' => $banner_type
            ];
        } else {
            list(, $banner_img) = explode(';', $banner_img);
            list(, $banner_img) = explode(',', $banner_img);
            $banner_img = base64_decode($banner_img);

            // Generate file path
            $db_file_path = 'uploads/banner/thump_' . time() . '.webp';
            $a_file_path = FCPATH . $db_file_path;
            file_put_contents($a_file_path, $banner_img);

            
            $data = [
                'category_id' => $category_id ?? 0,
                'banner_img'  => $db_file_path,
                'status' => $banner_type
            ];
        }

        $bannerModel = new BannerModel();


        if ($bannerModel->where('id', $banner_id)->set($data)->update()) {
            $output['success'] = true;
            $output['message'] = 'Banner updated.';
        } else {
            $output['message'] = 'Database error occurred.';
        }

        return $this->response->setJSON($output);
    }
}
