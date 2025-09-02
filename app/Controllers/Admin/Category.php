<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\CategoryModel;
use App\Models\CategoryGroupModel;
use App\Models\HeaderCategoryModel;
use App\Models\SettingsModel;

class Category extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            if (!can_view('category')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            $categoryGroupModel = new CategoryGroupModel();
            $data['groupcategories'] = $categoryGroupModel->findAll();

            return view('category/category', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function header()
    {
        $session = session();
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            if (!can_view('category')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            $categoryModel = new CategoryModel();
            $data['categories'] = $categoryModel->findAll();

            return view('category/headerCategory', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function headerAdd()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        // if (!can_add('category')) {
        //     $output = ['success' => false, "message" => "Permission not allowed"];
        //     return $this->response->setJSON($output);
        // }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $header_category_title = $this->request->getPost('header_category_title');
        $header_category_icon = $this->request->getPost('header_category_icon');
        $category_id = $this->request->getPost('category_id');
        $icon_library = $this->request->getPost('icon_library');

        // Validate and sanitize category name
        $header_category_title = filter_var($header_category_title, FILTER_SANITIZE_STRING);
        $header_category_title = str_replace("'", "’", $header_category_title);




        $headerCategoryModel = new HeaderCategoryModel();
        $data = [
            'title' => $header_category_title,
            'icon' => $header_category_icon,
            'category_id' => $category_id,
            'icon_library' => $icon_library
            
        ];
        if ($headerCategoryModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Group category added successfully.'
            ]);
        }
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to add group category.'
        ]);
    }

    public function headerList()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        // if (!can_view('category')) {
        //     $output = ['success' => false, "message" => "Permission not allowed"];
        //     return $this->response->setJSON($output);
        // }

        $headerCategoryModel = new HeaderCategoryModel();
        $categoryModel = new CategoryModel();
        $headerCategories = $headerCategoryModel->findAll();

        $output['data'] = [];


        foreach ($headerCategories as $row) {

            $categories = $categoryModel->where('id', $row['category_id'])->first();


            $action = "<a data-tooltip='tooltip' title='Edit Header Category' href='" . base_url("admin/header-category/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Header Category' onclick='deleteheadercategory(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";
                       
            $icon_library = '';
            
            if($row['icon_library'] == 1){
                $icon_library= 'MaterialDesignIcons';
            }elseif($row['icon_library'] == 2){
                $icon_library= 'FontAwesome';
            }elseif($row['icon_library'] == 3 || $row['icon_library'] == 0){
                $icon_library= 'Ionicons';
            }elseif($row['icon_library'] == 4){
                $icon_library= 'MaterialIcons';
            }

            $output['data'][] = [
                $row['id'],
                $row['title'],
                $row['icon'].'<br>'.$icon_library,
                $categories['category_name'] ?? '',
                $action,
            ];
        }

        return $this->response->setJSON($output);
    }

    public function headerEdit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('category')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $headerCategoryModel = new HeaderCategoryModel();
            $headercategory = $headerCategoryModel->where('id', $id)->first();

            $settingModel = new SettingsModel();
            $categoryModel = new CategoryModel();
            return view('category/editHeaderCategory', [
                'settings' => $settingModel->getSettings(),
                'categories' => $categoryModel->findAll(),
                'header_category' => $headercategory,
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function headerDelete()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $header_category_id = $this->request->getPost('header_category_id');

        $headerCategoryModel = new HeaderCategoryModel();

        if ($headerCategoryModel->delete($header_category_id)) {
            $output['success'] = true;
        } else {
            // Handle database insertion error
            $output['message'] = 'Database error occurred.';
        }



        return $this->response->setJSON($output);
    }

    public function headerUpdate()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $header_category_id = $this->request->getPost('header_category_id');
        $header_category_title = $this->request->getPost('header_category_title');
        $header_category_icon = $this->request->getPost('header_category_icon');
        $category_id = $this->request->getPost('category_id');
        $icon_library = $this->request->getPost('icon_library');

        // Validate and sanitize category name
        $header_category_title = filter_var($header_category_title, FILTER_SANITIZE_STRING);

        $data = [
            'title' => $header_category_title,
            'icon'  => $header_category_icon,
            'category_id' => $category_id,
            'icon_library' => $icon_library
        ];

        $headerCategoryModel = new HeaderCategoryModel();


        if ($headerCategoryModel->where('id', $header_category_id)->set($data)->update()) {
            $output['success'] = true;
            $output['message'] = 'Header category updated successfully.';
            
        } else {
            $output['message'] = 'Failed to update group category.';
        }


        return $this->response->setJSON($output);
    }


    public function group()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('category')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();


            return view('category/groupcategory', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function groupAdd()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        // if (!can_add('category')) {
        //     $output = ['success' => false, "message" => "Permission not allowed"];
        //     return $this->response->setJSON($output);
        // }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $group_category_title = $this->request->getPost('group_category_title');

        // Validate and sanitize category name
        $group_category_title = filter_var($group_category_title, FILTER_SANITIZE_STRING);
        $group_category_title = str_replace("'", "’", $group_category_title); // Replace single quotes with right single quote



        // Load the database model for posts
        $categoryGroupModel = new CategoryGroupModel();

        $data = [
            'title' => $group_category_title,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($categoryGroupModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Group category added successfully.'
            ]);
        }
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to add group category.'
        ]);
    }

    public function groupUpdate($id)
    {
        $categoryGroupModel = new CategoryGroupModel();

        $title = $this->request->getPost('group_category_title');

        if (!$title) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Group category is required'
            ]);
        }

        // Update record
        $updated = $categoryGroupModel->update($id, ['title' => $title]);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Group category updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update group category'
            ]);
        }
    }



    public function groupList()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_view('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $categoryGroupModel = new CategoryGroupModel();
        $groupcategories = $categoryGroupModel->findAll();
        $output['data'] = [];


        foreach ($groupcategories as $row) {
            $action = "<a data-tooltip='tooltip' title='Edit Group Category' href='" . base_url("admin/group-category/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Group Category' onclick='deletegroupcategory(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";

            $output['data'][] = [
                $row['id'],
                $row['title'],
                $action,
            ];
        }

        return $this->response->setJSON($output);
    }

    public function groupEdit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('category')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $categoryGroupModel = new CategoryGroupModel();
            $groupcategory = $categoryGroupModel->find($id);
            $settingModel = new SettingsModel();

            return view('category/editGroupCategory', [
                'settings' => $settingModel->getSettings(),

                'category_group' => $groupcategory
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function groupDelete()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_delete('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $group_category_id = $this->request->getPost('group_category_id');

        $categoryGroupModel = new CategoryGroupModel();

        if ($categoryGroupModel->delete($group_category_id)) {
            $output['success'] = true;
        } else {
            // Handle database insertion error
            $output['message'] = 'Database error occurred.';
        }



        return $this->response->setJSON($output);
    }

    public function list()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        if (!can_view('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $categoryModel = new CategoryModel();
        $categoryGroupModel = new CategoryGroupModel();
        $categories = $categoryModel->getCategoriesWithSubCount();

        $output['data'] = [];


        foreach ($categories as $row) {

            $groupcategories = $categoryGroupModel->where('id', $row['category_group_id'])->first();

            $img = "<a href='" . base_url($row['category_img']) . "' target='_blank'>
                        <img class='media-object round-media' src='" . base_url($row['category_img']) . "' alt='image' style='height: 75px; width: 40%'>
                    </a>";
            $action = "<a data-tooltip='tooltip' title='Edit Category' href='" . base_url("admin/category/edit/{$row['id']}") . "' class='btn btn-primary-light btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Category' onclick='deletecategory(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";

            $output['data'][] = [
                $row['id'],
                $row['category_name'],
                $img,
                $row['sub_count'],
                $groupcategories['title'] ?? '',
                $action,
            ];
        }

        return $this->response->setJSON($output);
    }
    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('category')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $categoryModel = new CategoryModel();
            $category = $categoryModel->find($id);

            $settingModel = new SettingsModel();
            $categoryGroupModel = new CategoryGroupModel();

            return view('category/editCategory', [
                'settings' => $settingModel->getSettings(),
                'category' => $category,
                'groupcategories' => $categoryGroupModel->findAll()
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
        if (!can_add('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $cat_name = $this->request->getPost('cat_name');
        $is_bestseller_category = $this->request->getPost('is_bestseller_category');
        $cat_img = $this->request->getPost('cat_img');
        $category_group_id = $this->request->getPost('category_group_id');
        $is_it_have_warning = $this->request->getPost('is_it_have_warning');
        $warning_content = $this->request->getPost('warning_content');

        // Validate and sanitize category name
        $cat_name = str_replace("'", "’", $cat_name); // Replace single quotes with right single quote

        // Generate the initial slug
        $slug_prev = str_replace(" ", "-", $cat_name);
        $slug = preg_replace('/[^A-Za-z0-9-]/', '', strtolower($slug_prev));
        $slug1 = $slug;

        // Load the database model for posts
        $categoryModel = new CategoryModel();

        // Check if slug is available, add suffix if needed
        $check = true;
        $x = 1;
        while ($check) {
            $duplicateSlug = $categoryModel->where('slug', $slug1)->countAllResults();

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
        if (strpos($cat_img, 'data:image') === 0) {
            list(, $cat_img) = explode(';', $cat_img);
            list(, $cat_img) = explode(',', $cat_img);
            $cat_img = base64_decode($cat_img);

            // Generate file path
            $db_file_path = 'uploads/category/thump_' . time() . '.webp';
            $a_file_path = FCPATH . $db_file_path;

            // Write image to file
            if (file_put_contents($a_file_path, $cat_img) !== false) {
                // Insert category into database
                $categoryModel = new CategoryModel();
                $data = [
                    'category_name' => $cat_name,
                    'is_bestseller_category' => $is_bestseller_category,
                    'category_img'  => $db_file_path,
                    'slug'  => $slug1,
                    'category_group_id' => $category_group_id ?? 0,
                    'is_it_have_warning' => $is_it_have_warning,
                    'warning_content' => $warning_content
                ];

                if ($categoryModel->insert($data)) {
                    $output['success'] = true;
                    $output['message'] = 'Category added successfully';
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
        if (!can_delete('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $cat_id = $this->request->getPost('cat_id');

        $categoryModel = new CategoryModel();

        if ($categoryModel->delete($cat_id)) {
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
        if (!can_edit('category')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $cat_name = $this->request->getPost('cat_name');
        $cat_id = $this->request->getPost('cat_id');
        $cat_img = $this->request->getPost('files');
        $is_bestseller_category = $this->request->getPost('is_bestseller_category');
        $is_it_have_warning = $this->request->getPost('is_it_have_warning');
        $warning_content = $this->request->getPost('warning_content');

        // Validate and sanitize category name
        $cat_name = filter_var($cat_name, FILTER_SANITIZE_STRING);

        if ($cat_img == "") {
            $data = [
                'category_name' => $cat_name,
                'is_bestseller_category' => $is_bestseller_category,
                'is_it_have_warning' => $is_it_have_warning,
                    'warning_content' => $warning_content
            ];
        } else {
            list(, $cat_img) = explode(';', $cat_img);
            list(, $cat_img) = explode(',', $cat_img);
            $cat_img = base64_decode($cat_img);

            // Generate file path
            $db_file_path = 'uploads/category/thump_' . time() . '.webp';
            $a_file_path = FCPATH . $db_file_path;
            file_put_contents($a_file_path, $cat_img);
            $data = [
                'category_name' => $cat_name,
                'category_img'  => $db_file_path,
                'is_bestseller_category' => $is_bestseller_category,
                'is_it_have_warning' => $is_it_have_warning,
                    'warning_content' => $warning_content

            ];
        }

        $categoryModel = new CategoryModel();


        if ($categoryModel->where('id', $cat_id)->set($data)->update()) {
            $output['success'] = true;
            $output['message'] = 'Category updated successfully.';
        } else {
            $output['message'] = 'Failed to update category..';
        }

        return $this->response->setJSON($output);
    }
    public function categoryOrder()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('category-order')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->orderBy('row_order')->findAll();
            $settingModel = new SettingsModel();

            return view('category/categoryOrder', [
                'settings' => $settingModel->getSettings(),

                'categories' => $categories
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function categoryOrderUpdate()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('category-order')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $request = $this->request->getJSON();
        $order = $request->order;

        $category_model = new CategoryModel();

        foreach ($order as $index => $productId) {
            // Update the row_order field in the database based on the new order
            $category_model->update($productId, ['row_order' => $index + 1]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Category order updated']);
    }
}
