<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\ProductModel;
use App\Models\ProductTagModel;
use App\Models\ProductVariantsModel;
use App\Models\SellerModel;
use App\Models\TagsModel;
use App\Models\UserModel;

class Search extends BaseController
{
    public function index()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $user = null;

        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        // Redirect to loader if no city is selected
        if (session()->get('city_id') == null) {
            $data['session_load'] = 0;
            return view('website/loader', $data);
        }

        if ($this->request->is('get')) {
            return view('website/search/search', $data);
        }

        if ($this->request->is('post')) {
            $dataInput = $this->request->getJSON(true);

            $searchTerm = $dataInput['searchStr'];

            $sellerModel = new SellerModel();
            $productVariantModel = new ProductVariantsModel();
            $cartsModel = new CartsModel();
            $productModel = new ProductModel();
            $tagsModel = new TagsModel();
            $productTagModel = new ProductTagModel();
            $products = [];

            // Check if session is set and get user details
            $userModel = new UserModel();
            $user = null;
            if (session()->get('login_type') == 'email') {
                $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (session()->get('login_type') == 'mobile') {
                $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            // Fetch matching tags based on search query
            $tags = $tagsModel->like('name', $searchTerm)->findAll();
            $tagIds = array_column($tags, 'id');

            // Get product IDs from tags
            $productTags = !empty($tagIds) ? $productTagModel->whereIn('tag_id', $tagIds)->findAll() : [];
            $tagProductIds = array_column($productTags, 'product_id');

            // Get sellers based on city ID
            $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();

            foreach ($sellers as $seller) {
                // Get all products from each seller that match the search term in name OR by tags
                $productQuery = $productModel
                    ->where('is_delete', 0)
                    ->where('seller_id', $seller['id'])
                    ->groupStart()
                    ->like('product_name', $searchTerm);

                // Add tag-based search
                if (!empty($tagProductIds)) {
                    $productQuery->orWhereIn('id', $tagProductIds);
                }

                $sellerProducts = $productQuery->groupEnd()->findAll();

                foreach ($sellerProducts as &$product) {
                    // Get product variants
                    $product['variants'] = $productVariantModel
                        ->where('product_id', $product['id'])
                        ->where('is_delete', 0)
                        ->findAll();

                    // Calculate discount percentage for each variant
                    foreach ($product['variants'] as &$variant) {
                        if ($variant['price'] > 0 && $variant['discounted_price'] > 0) {
                            $variant['discountPercentage'] = round((($variant['price'] - $variant['discounted_price']) / $variant['price']) * 100);
                        } else {
                            $variant['discountPercentage'] = 0; // Set to 0 if price or discounted_price is invalid
                        }
                    }

                    // If user is logged in, check if the product is in their cart
                    $product['cart_quantity'] = 0; // Default to 0 if not in cart
                    if ($user) {
                        $cartItem = $cartsModel->where('user_id', $user['id'])
                            ->where('product_id', $product['id'])
                            ->first();

                        if ($cartItem) {
                            $product['cart_quantity'] = $cartItem['quantity'];
                        }
                    }
                }

                // Merge seller products into the main products array
                $products = array_merge($products, $sellerProducts);
            }

            $data['products'] = $products;
            return $this->response->setJSON(['status' => 'success', 'products' => $data['products'], 'currency_symbol_position' => $this->settings['currency_symbol_position'], 'currency_symbol' => $this->country['currency_symbol']]);
        }
    }
}
