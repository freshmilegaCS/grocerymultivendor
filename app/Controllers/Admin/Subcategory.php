<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\SubcategoryModel;
use App\Models\CategoryModel;
use App\Models\SellerModel;
use App\Models\SettingsModel;

class Subcategory extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('subcategory')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->getCategories();
            $settingModel = new SettingsModel();

            return view('subcategory/subcategory', [
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
        if (!can_view('subcategory')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $subcategoryModel = new SubcategoryModel();
        $subcategories = $subcategoryModel->getSubcategoriesWithDetails();
        $output['data'] = [];

        foreach ($subcategories as $row) {
            $img = "<a href='" . base_url($row['img']) . "' target='_blank'>
                        <img class='media-object round-media' src='" . base_url($row['img']) . "' alt='image' style='height: 70px; width: 40%'>
                    </a>";
            $action = "<a data-tooltip='tooltip' title='Edit SubCategory' href='" . base_url("admin/subcategory/edit/{$row['id']}") . "' class='btn btn-primary-light  btn-xs'>
                        <i class='fi fi-tr-customize-edit'></i>
                       </a> <a type='button' data-tooltip='tooltip' title='Delete Category' onclick='deletesubcategory(" . $row['id'] . ")' class='btn btn-danger-light btn-xs'><i class='fi fi-tr-trash-xmark'>  </i> </a>";

            $output['data'][] = [
                $row['id'],
                $row['category_name'],  // Category name from joined data
                $row['name'],           // Subcategory name
                $img,
                $row['product_count'],  // Number of products in this subcategory
                $action,
            ];
        }

        return $this->response->setJSON($output);
    }
    public function add()
    {
        $output = ['success' => false];

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('subcategory')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }

        // Ensure the request is POST and has the required data
        if ($this->validate([
            'cat_id' => 'required|is_natural_no_zero',
            'sub_cat_name' => 'required',
            'sub_cat_img' => 'required',
        ])) {
            $cat_id = $this->request->getPost('cat_id');
            $sub_cat_name = $this->request->getPost('sub_cat_name');
            $sub_cat_img = $this->request->getPost('sub_cat_img');
            // Generate the initial slug
            $slug_prev = str_replace(" ", "-", $sub_cat_name);
            $slug = preg_replace('/[^A-Za-z0-9-]/', '', strtolower($slug_prev));
            $slug1 = $slug;

            // Load the database model for posts
            $subcategoryModel = new SubcategoryModel();

            // Check if slug is available, add suffix if needed
            $check = true;
            $x = 1;
            while ($check) {
                $duplicateSlug = $subcategoryModel->where('slug', $slug1)->countAllResults();

                if ($duplicateSlug > 0) {
                    // If a duplicate slug is found, append a number to the slug
                    $slug1 = $slug . $x;
                } else {
                    // Slug is unique, break the loop
                    $check = false;
                }
                $x++;
            }
            // Check if the image data is valid
            if (strpos($sub_cat_img, 'data:image') === 0) {
                list($type, $sub_cat_img) = explode(';', $sub_cat_img);
                list(, $sub_cat_img) = explode(',', $sub_cat_img);
                $sub_cat_img = base64_decode($sub_cat_img);

                // Generate file path
                $db_file_path = 'uploads/subcategory/subcat_' . time() . '.webp';
                $a_file_path = FCPATH . $db_file_path;

                // Write image data to file
                if (file_put_contents($a_file_path, $sub_cat_img) !== false) {
                    $subcategoryModel = new SubcategoryModel();

                    $data = [
                        'category_id' => $cat_id,
                        'name' => htmlspecialchars($sub_cat_name, ENT_QUOTES, 'UTF-8'),
                        'img' => $db_file_path,
                        'slug' => $slug1
                    ];

                    if ($subcategoryModel->insertSubcategory($data)) {
                        $output['success'] = true;
                    } else {
                        // Handle database error
                        $output['message'] = 'Failed to insert into database.';
                    }
                } else {
                    // Handle file write error
                    $output['message'] = 'Failed to write the image file.';
                }
            } else {
                // Handle invalid image data
                $output['message'] = 'Invalid image data.';
            }
        } else {
            // Handle validation errors
            $output['message'] = 'Invalid input data.';
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
        if (!can_delete('subcategory')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        // Get POST data
        $subcat_id = $this->request->getPost('subcat_id');

        $subcategoryModel = new SubcategoryModel();

        if ($subcategoryModel->delete($subcat_id)) {
            $output['success'] = true;
        } else {
            // Handle database insertion error
            $output['message'] = 'Database error occurred.';
        }



        return $this->response->setJSON($output);
    }

    public function edit($id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('subcategory')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $subcategoryModel = new SubcategoryModel();
            $subcategory = $subcategoryModel->find($id);
            $categoryModel = new CategoryModel();
            $settingModel = new SettingsModel();

            $categories = $categoryModel->getCategories();
            return view('subcategory/editSubcategory', [
                'categories' => $categories,
                'settings' => $settingModel->getSettings(), 
                'subcategory' => $subcategory
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
        if (!can_edit('subcategory')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        
        // Get POST data
        $sub_cat_name = $this->request->getPost('sub_cat_name');
        $cat_id = $this->request->getPost('cat_id');
        $sub_cat_id = $this->request->getPost('sub_cat_id');
        $sub_cat_img = $this->request->getPost('sub_cat_img');

        // Validate and sanitize category name
        $sub_cat_name = filter_var($sub_cat_name, FILTER_SANITIZE_STRING);

        if ($sub_cat_img == "") {
            $data = [
                'name' => $sub_cat_name,
                'category_id' => $cat_id,
            ];
        } else {
            list($type, $sub_cat_img) = explode(';', $sub_cat_img);
            list(, $sub_cat_img) = explode(',', $sub_cat_img);
            $sub_cat_img = base64_decode($sub_cat_img);

            // Generate file path
            $db_file_path = 'uploads/subcategory/thump_' . time() . '.webp';
            $a_file_path = FCPATH . $db_file_path;
            file_put_contents($a_file_path, $sub_cat_img);
            $data = [
                'name' => $sub_cat_name,
                'category_id' => $cat_id,
                'img'  => $db_file_path
            ];
        }

        $subcategoryModel = new SubcategoryModel();


        if ($subcategoryModel->where('id', $sub_cat_id)->set($data)->update()) {
            $output['success'] = true;
        } else {
            $output['message'] = 'Database error occurred.';
        }

        return $this->response->setJSON($output);
    }

    public function getSub()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        $categoryId = $this->request->getPost('cat_change');

        if ($categoryId) {
            $subcategoryModel = new SubcategoryModel();
            $sellerModel = new SellerModel();
            $data['subcategory'] = $subcategoryModel->getSubcategoriesByCategoryId($categoryId);
            $data['seller'] = $sellerModel->select('seller.id, seller.store_name')
                ->join('seller_categories', 'seller_categories.seller_id = seller.id', 'left')
                ->where('seller_categories.category_id', $categoryId)
                ->findAll();

            return $this->response->setJSON($data);
        } else {
            return $this->response->setJSON(['error' => 'Invalid category']);
        }
    }
    public function subcategoryOrder()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('subcategory-order')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->orderBy('row_order')->findAll();

            return view('subcategory/subcategoryOrder', [
                'settings' => $settingModel->getSettings(), 
                'categories' => $categories
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function subcategoryOrderUpdate()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_edit('subcategory-order')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        if ($this->settings['demo_mode']) {
            $output = ['success' => false, "message" => "Demo Mode! Permission not allowed"];
            return $this->response->setJSON($output);
        }
        $request = $this->request->getJSON();
        $order = $request->order;

        $SubcategoryModel = new SubcategoryModel();

        foreach ($order as $index => $productId) {
            // Update the row_order field in the database based on the new order
            $SubcategoryModel->update($productId, ['row_order' => $index + 1]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Subcategory order updated']);
    }
}
