<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;

use App\Models\ProductVariantsModel;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\SettingsModel;

class StockMangement extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $categoryModel = new CategoryModel();
            $categories = $categoryModel->getCategories();
            return view('sellerPanel/product/stockManagement', [
                'settings' => $settingModel->getSettings(), 
                'categories' => $categories,
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function list()
    {
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }


        $category = $this->request->getPost('category');
        $status = $this->request->getPost('status');
        $stock = $this->request->getPost('stock');



        $productModel = new ProductModel();
        $products = $productModel->getProductsList($category, session()->get('user_id'),  $status, $stock);
        $output['data'] = [];
        $x = 1;
        foreach ($products as $row) {
            foreach ($row['variants'] as $variants) {
                $img = "<a href='" . base_url($row['main_img']) . "' target='_blank'>
                    <img class='media-object round-media' src='" . base_url($row['main_img']) . "' alt='image' style='height: 60px; width: 40%'>
                </a>";

                // Stock status

                // Publish status
                $publish = $row['status'] == 1 ? "<span class='badge badge-success'>Published</span> " : "<span class='badge badge-danger'>Unpublish</span> ";
                if ($variants['is_unlimited_stock']) {
                    $stock =  "<span class='badge badge-success'>Unlimited</span>";
                } else {
                    $stock = "<div class='stock-cell' data-id='{$variants['id']}' style='cursor: pointer;'>
                <span class='editable-stock' data-id='{$variants['id']}'>{$variants['stock']}</span>
                <div class='input-group input-group-sm'>
                <input type='text' class='form-control stock-input' data-id='{$variants['id']}' value='{$variants['stock']}' style='display:none; ' >
                <span class='input-group-append'>
                  <button type='button' class='update-stock-btn btn btn-primary' data-id='{$variants['id']}' style='display:none;'><i class='fi fi-tr-cloud-upload'></i></button>
                </span>
              </div>
              </div>";
                }
                // Product badges
                $productname = $row['product_name'];


                // Prepare the output data
                $output['data'][] = [
                    $variants['id'],
                    $productname,
                    $row['store_name'],  // Category name
                    $img,
                    $variants['title'],
                    $stock,
                    $publish
                ];
                $x++;
            }
        }

        return $this->response->setJSON($output);
    }


    public function update()
    {
        $output = ['success' => false];
        // Ensure session is started
        if (!session()->has('user_id') || session('account_type') != 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }

        $productVariantId = $this->request->getPost('variant_id');
        $newStock = $this->request->getPost('stock');
        $userId = session('user_id'); // Get the logged-in user's ID

        $productVariantModel = new ProductVariantsModel();

        $productVariantModel->select('product_variants.id')
            ->join('product', 'product.id = product_variants.product_id', 'left')
            ->where('product_variants.id', $productVariantId)
            ->where('product.seller_id', $userId); // Ensure the product belongs to the logged-in user

        $variant = $productVariantModel->first();
        if (!$variant) {
            $output['message'] = 'Invalid product or unauthorized access';
            return $this->response->setJSON($output);
        }

        // Update stock if the variant exists
        $data = ['stock' => $newStock];
        if ($productVariantModel->update($productVariantId, $data)) {
            $output['success'] = true;
            $output['message'] = 'Stock updated successfully';
        } else {
            $output['message'] = 'Something went wrong';
        }

        return $this->response->setJSON($output);
    }
}
