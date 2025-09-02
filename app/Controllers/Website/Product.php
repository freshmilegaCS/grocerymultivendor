<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\BrandModel;
use App\Models\CartsModel;
use App\Models\CategoryModel;
use App\Models\OrderProductModel;
use App\Models\ProductImagesModel;
use App\Models\ProductModel;
use App\Models\ProductRatingsModel;
use App\Models\ProductVariantsModel;
use App\Models\SellerModel;
use App\Models\SubcategoryModel;
use App\Models\UserModel;
use App\Models\ProductSortTypeModel;


class Product extends BaseController
{
    public function index()
    {
        $data['settings'] = $this->settings;

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


        $categoryModel = new CategoryModel();
        $data['categorys'] = $this->settings['frontend_category_section'] == 1 ? $categoryModel->orderBy('row_order', 'asc')->findAll() : [];

        $brandModel = new BrandModel();
        $data['brands'] = $this->settings['frontend_brand_section'] == 1 ? $brandModel->orderBy('row_order', 'asc')->findAll() : [];

        $sellerModel = new SellerModel();
        $data['sellers'] = $this->settings['frontend_seller_section'] == 1 ? $sellerModel->where('city_id', session()->get('city_id'))->findAll() : [];

        $productSortTypeModel = new ProductSortTypeModel();
        $data['productSorts'] = $productSortTypeModel->findAll();

        $data['is_mobile'] = preg_match('/(iphone|ipod|android|blackberry|mobile|tablet|kindle|mobi|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);

        return view('website/product/product', $data);
    }

    public function getPopularProductWithVariants()
    {
        $data['settings'] = $this->settings;
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

        $categoryModel = new CategoryModel();
        $data['categorys'] = $this->settings['frontend_category_section'] == 1 ? $categoryModel->orderBy('row_order', 'asc')->findAll() : [];

        $brandModel = new BrandModel();
        $data['brands'] = $this->settings['frontend_brand_section'] == 1 ? $brandModel->orderBy('row_order', 'asc')->findAll() : [];

        $sellerModel = new SellerModel();
        $data['sellers'] = $this->settings['frontend_seller_section'] == 1 ? $sellerModel->where('city_id', session()->get('city_id'))->findAll() : [];

        $productSortTypeModel = new ProductSortTypeModel();
        $data['productSorts'] = $productSortTypeModel->findAll();

        $data['is_mobile'] = preg_match('/(iphone|ipod|android|blackberry|mobile|tablet|kindle|mobi|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);

        $data['is_popular'] = true;
        return view('website/product/product', $data);
    }

    public function getDealoftheDayProductWithVariants()
    {
        $data['settings'] = $this->settings;
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

        $categoryModel = new CategoryModel();
        $data['categorys'] = $this->settings['frontend_category_section'] == 1 ? $categoryModel->orderBy('row_order', 'asc')->findAll() : [];

        $brandModel = new BrandModel();
        $data['brands'] = $this->settings['frontend_brand_section'] == 1 ? $brandModel->orderBy('row_order', 'asc')->findAll() : [];

        $sellerModel = new SellerModel();
        $data['sellers'] = $this->settings['frontend_seller_section'] == 1 ? $sellerModel->where('city_id', session()->get('city_id'))->findAll() : [];

        $productSortTypeModel = new ProductSortTypeModel();
        $data['productSorts'] = $productSortTypeModel->findAll();

        $data['is_mobile'] = preg_match('/(iphone|ipod|android|blackberry|mobile|tablet|kindle|mobi|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);

        $data['is_dealoftheday'] = true;
        return view('website/product/product', $data);
    }

    public function getBrandProductList($brand_slug)
    {
        $data['settings'] = $this->settings;
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

        $categoryModel = new CategoryModel();
        $data['categorys'] = $this->settings['frontend_category_section'] == 1 ? $categoryModel->orderBy('row_order', 'asc')->findAll() : [];

        $brandModel = new BrandModel();
        $data['brands'] =  $brandModel->orderBy('row_order', 'asc')->findAll();

        $sellerModel = new SellerModel();
        $data['sellers'] = $this->settings['frontend_seller_section'] == 1 ? $sellerModel->where('city_id', session()->get('city_id'))->findAll() : [];

        $productSortTypeModel = new ProductSortTypeModel();
        $data['productSorts'] = $productSortTypeModel->findAll();

        $data['is_mobile'] = preg_match('/(iphone|ipod|android|blackberry|mobile|tablet|kindle|mobi|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);

        $data['is_brand'] = true;
        $data['brand_slug'] = $brand_slug;
        return view('website/product/product', $data);
    }

    public function getSellerProductList($seller_slug)
    {
        $data['settings'] = $this->settings;
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

        $categoryModel = new CategoryModel();
        $data['categorys'] = $this->settings['frontend_category_section'] == 1 ? $categoryModel->orderBy('row_order', 'asc')->findAll() : [];

        $brandModel = new BrandModel();
        $data['brands'] = $this->settings['frontend_brand_section'] == 1 ? $brandModel->orderBy('row_order', 'asc')->findAll() : [];

        $sellerModel = new SellerModel();
        $data['sellers'] = $this->settings['frontend_seller_section'] == 1 ? $sellerModel->where('city_id', session()->get('city_id'))->findAll() : [];

        $productSortTypeModel = new ProductSortTypeModel();
        $data['productSorts'] = $productSortTypeModel->findAll();

        $data['is_mobile'] = preg_match('/(iphone|ipod|android|blackberry|mobile|tablet|kindle|mobi|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);

        $data['is_seller'] = true;
        $data['seller_slug'] = $seller_slug;
        return view('website/product/product', $data);
    }

    public function fetchProductList()
    {
        $dataInput = $this->request->getJSON(true);

        $settings = $this->settings;
        $country = $this->country;

        $categories = $dataInput['categorys'];
        $brands = $dataInput['brands'];
        $sellers_array = $dataInput['sellers'];
        $fromPrice = (int)$dataInput['fromPrice'];
        $toPrice = (int)$dataInput['toPrice'];

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $productVariantsModel = new ProductVariantsModel();
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();
        $brandModel = new BrandModel();

        $products = []; // Initialize the products array.
        $user = null;

        // Fetch the logged-in user
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        // Fetch cart item count for the user
        $data['cartItemCount'] = $user
            ? $cartsModel->where('user_id', $user['id'])->countAllResults()
            : 0;

        // Fetch sellers based on the user's city
        $sellerModel = new SellerModel();
        if (empty($sellers_array)) {
            $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();
        } else {
            $sellers = $sellerModel->where('city_id', session()->get('city_id'))->whereIn('slug', $sellers_array)->findAll();
        }

        // Fetch category and brand IDs from their slugs
        $categoryIds = !empty($categories) ? $categoryModel->whereIn('slug', $categories)->findAll() : [];
        $brandIds = !empty($brands) ? $brandModel->whereIn('slug', $brands)->findAll() : [];

        // Extract the IDs of the categories and brands
        $categoryIds = !empty($categoryIds) ? array_column($categoryIds, 'id') : [];
        $brandIds = !empty($brandIds) ? array_column($brandIds, 'id') : [];

        $productIds = [];

        $minPrice = $productVariantsModel
            ->where('is_delete', 0)
            ->selectMin('discounted_price')
            ->first()['discounted_price'];

        $maxPrice = $productVariantsModel
            ->where('is_delete', 0)
            ->selectMax('price')
            ->first()['price'];


        if ($fromPrice > 0 && $toPrice > 0) {
            // Fetch product variant IDs within the specified price range
            $rangeProductVariants = $productVariantsModel
                ->select('product_id')
                ->groupStart()
                ->where('discounted_price', 0)
                ->where('price >=', $fromPrice)
                ->where('price <=', $toPrice)
                ->groupEnd()
                ->orGroupStart()
                ->where('discounted_price >', 0)
                ->where('discounted_price >=', $fromPrice)
                ->where('discounted_price <=', $toPrice)
                ->groupEnd()
                ->distinct()
                ->findAll();


            // Extract the product IDs into an array
            $variantProductIds = array_column($rangeProductVariants, 'product_id');

            // Fetch product IDs that match the seller and range filter
            if (!empty($variantProductIds)) {
                foreach ($sellers as $seller) {
                    $sellerProductIds = $productModel
                        ->select('id') // Only fetch product IDs
                        ->where('seller_id', $seller['id'])
                        ->where('status', 1)
                        ->whereIn('id', $variantProductIds)
                        ->findAll();

                    // Merge results into $productIds
                    $productIds = array_merge($productIds, array_column($sellerProductIds, 'id'));
                }

                // Ensure the product IDs are unique
                $productIds = array_unique($productIds);
            }
        } else {
            $fromPrice = $productVariantsModel
                ->where('is_delete', 0)
                ->selectMin('discounted_price')
                ->first()['discounted_price'];

            $toPrice = $productVariantsModel
                ->where('is_delete', 0)
                ->selectMax('price')
                ->first()['price'];
        }

        foreach ($sellers as $seller) {
            // Apply sorting and filter logic in a single switch statement
            $productQuery = $productModel->where('is_delete', 0)
                ->where('status', 1)
                ->where('seller_id', $seller['id']);

            // Apply category filter if selected
            if (!empty($categoryIds)) {
                $productQuery->whereIn('category_id', $categoryIds);
            }

            // Apply brand filter if selected
            if (!empty($brandIds)) {
                $productQuery->whereIn('brand_id', $brandIds);
            }

            if (!empty($productIds)) {
                $productQuery->whereIn('id', $productIds);
            }

            // Determine product sorting based on `productSort` value
            switch ($dataInput['productSort']) {
                case 1:
                    // Default sorting without additional order
                    $productQuery->orderBy('row_order', 'ASC');
                    $sellerProducts = $productQuery->findAll();
                    break;

                case 2:
                    // Sort by price (Low to High)
                    $sellerProducts = $productQuery->findAll();
                    foreach ($sellerProducts as &$product) {
                        // Fetch product variants
                        $product['variants'] = $productVariantsModel
                            ->where('product_id', $product['id'])
                            ->where('is_delete', 0)
                            ->findAll();
                    }
                    usort($sellerProducts, function ($a, $b) {
                        $aPrice = isset($a['variants'][0]['discounted_price']) && $a['variants'][0]['discounted_price'] > 0
                            ? $a['variants'][0]['discounted_price']
                            : (isset($a['variants'][0]['price']) ? $a['variants'][0]['price'] : PHP_INT_MAX);

                        $bPrice = isset($b['variants'][0]['discounted_price']) && $b['variants'][0]['discounted_price'] > 0
                            ? $b['variants'][0]['discounted_price']
                            : (isset($b['variants'][0]['price']) ? $b['variants'][0]['price'] : PHP_INT_MAX);

                        return $aPrice <=> $bPrice;
                    });
                    break;

                case 3:
                    // Sort by price (High to Low)
                    $sellerProducts = $productQuery->findAll();
                    foreach ($sellerProducts as &$product) {
                        // Fetch product variants
                        $product['variants'] = $productVariantsModel
                            ->where('product_id', $product['id'])
                            ->where('is_delete', 0)
                            ->findAll();
                    }
                    usort($sellerProducts, function ($a, $b) {
                        $aPrice = isset($a['variants'][0]['discounted_price']) && $a['variants'][0]['discounted_price'] > 0
                            ? $a['variants'][0]['discounted_price']
                            : (isset($a['variants'][0]['price']) ? $a['variants'][0]['price'] : -PHP_INT_MAX);

                        $bPrice = isset($b['variants'][0]['discounted_price']) && $b['variants'][0]['discounted_price'] > 0
                            ? $b['variants'][0]['discounted_price']
                            : (isset($b['variants'][0]['price']) ? $b['variants'][0]['price'] : -PHP_INT_MAX);

                        return $bPrice <=> $aPrice;
                    });
                    break;


                case 4:
                    // Sort by discount (High to Low)
                    $sellerProducts = $productQuery->findAll();
                    foreach ($sellerProducts as &$product) {
                        // Fetch product variants
                        $product['variants'] = $productVariantsModel
                            ->where('product_id', $product['id'])
                            ->where('is_delete', 0)
                            ->findAll();
                    }
                    usort($sellerProducts, function ($a, $b) {
                        // Calculate discount percentage for product A
                        if (isset($a['variants'][0]['price']) && isset($a['variants'][0]['discounted_price']) && $a['variants'][0]['discounted_price'] > 0) {
                            $aDiscountPercentage = (($a['variants'][0]['price'] - $a['variants'][0]['discounted_price']) / $a['variants'][0]['price']) * 100;
                        } else {
                            $aDiscountPercentage = 0; // No discount
                        }

                        // Calculate discount percentage for product B
                        if (isset($b['variants'][0]['price']) && isset($b['variants'][0]['discounted_price']) && $b['variants'][0]['discounted_price'] > 0) {
                            $bDiscountPercentage = (($b['variants'][0]['price'] - $b['variants'][0]['discounted_price']) / $b['variants'][0]['price']) * 100;
                        } else {
                            $bDiscountPercentage = 0; // No discount
                        }

                        // Compare discount percentages in descending order (High to Low)
                        return $bDiscountPercentage <=> $aDiscountPercentage;
                    });
                    break;



                case 5:
                    // Sort by product name ascending
                    $sellerProducts = $productQuery->orderBy('product_name', 'ASC')->findAll();
                    break;

                case 6:
                    // Sort by product popular
                    $sellerProducts = $productQuery->where('popular', 1)->findAll();
                    break;

                case 7:
                    // Sort by product deal of the day
                    $sellerProducts = $productQuery->where('deal_of_the_day', 1)->findAll();
                    break;

                // Add other sorting cases if needed
                default:
                    $sellerProducts = $productQuery->findAll();
                    break;
            }

            foreach ($sellerProducts as $product) {
                // 2) Fetch non-deleted variants for this product
                $variants = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0)
                    ->findAll();

                // 3) Only proceed if we actually got at least one variant
                if (empty($variants)) {
                    // no variants → skip this product
                    continue;
                }

                // 4) Attach the variants array
                $product['variants'] = $variants;

                // 5) Default cart_quantity to zero
                $product['cart_quantity'] = 0;

                // 6) If the user is logged in, see if they have this in their cart
                if (isset($user['id'])) {
                    $cartItem = $cartsModel
                        ->where('user_id', $user['id'])
                        ->where('product_id', $product['id'])
                        ->first();
                    if ($cartItem) {
                        $product['cart_quantity'] = (int) $cartItem['quantity'];
                    }
                }

                // 7) Finally, push into your output array
                $products[] = $product;
            }
        }



        // Return response with sorted products
        return $this->response->setJSON([
            'status' => 'success',
            'products' => $products,
            'minPrice' => (int)$minPrice,
            'maxPrice' => (int)$maxPrice,
            'fromPrice' => (int)$fromPrice,
            'toPrice' => (int)$toPrice,
            'base_url' => base_url(),
            'currency_symbol' => $country['currency_symbol'],
            'currency_symbol_position' => $settings['currency_symbol_position'],
        ]);
    }

    public function getProductDetails($slug)
    {
        // Load necessary models
        $productModel = new ProductModel();
        $productImagesModel = new ProductImagesModel();
        $productRatingsModel = new ProductRatingsModel();
        $productVariantsModel = new ProductVariantsModel();
        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();
        $brandModel = new BrandModel();
        $sellerModel = new SellerModel();
        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $user = null;

        // Check for user session
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $data['cartItemCount'] = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['user'] = $user;
        }

        // if (!session()->get('city_id')) {
        //     return view('website/loader', $data);
        // }

        // Fetch product details based on slug
        $product = $productModel->where('slug', $slug)
            ->where('status', 1)->where('is_delete', 0)->first();

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Initialize images array with main_img as the first element
        $product['images'] = [['id' => null, 'image' => $product['main_img']]];

        // Fetch additional product images
        $additionalImages = $productImagesModel->where('product_id', $product['id'])->findAll();
        $product['images'] = array_merge($product['images'], $additionalImages);

        // Fetch product variants
        $product['variants'] = $productVariantsModel->where('product_id', $product['id'])->where('is_delete', 0)->findAll();

        // For each variant, check if it is in the user's cart and set cart quantity
        foreach ($product['variants'] as &$variant) {
            $cartItem = $user ? $cartsModel
                ->where('user_id', $user['id'])
                ->where('product_variant_id', $variant['id'])
                ->first() : null;

            // Set cart quantity if variant is in the cart, otherwise default to 0
            $variant['cart_quantity'] = $cartItem ? $cartItem['quantity'] : 0;
        }

        // Fetch product ratings and calculate average rating
        $ratings = $productRatingsModel->where('product_id', $product['id'])->where('is_approved_to_show', 1)->where('is_active', 1)->where('is_delete', 0)->findAll();
        $totalRatingCount = count($ratings);
        $averageRating = $totalRatingCount > 0 ? array_sum(array_column($ratings, 'rate')) / $totalRatingCount : 0;
        $product['average_rating'] = round($averageRating, 1);
        $product['rating_count'] = $totalRatingCount;

        // Count ratings by star value (1 to 5)
        $product['star_ratings'] = [
            '1_star' => $productRatingsModel->where('product_id', $product['id'])->where('rate', 1)->where('is_approved_to_show', 1)->where('is_active', 1)->where('is_delete', 0)->countAllResults(),
            '2_star' => $productRatingsModel->where('product_id', $product['id'])->where('rate', 2)->where('is_approved_to_show', 1)->where('is_active', 1)->where('is_delete', 0)->countAllResults(),
            '3_star' => $productRatingsModel->where('product_id', $product['id'])->where('rate', 3)->where('is_approved_to_show', 1)->where('is_active', 1)->where('is_delete', 0)->countAllResults(),
            '4_star' => $productRatingsModel->where('product_id', $product['id'])->where('rate', 4)->where('is_approved_to_show', 1)->where('is_active', 1)->where('is_delete', 0)->countAllResults(),
            '5_star' => $productRatingsModel->where('product_id', $product['id'])->where('rate', 5)->where('is_approved_to_show', 1)->where('is_active', 1)->where('is_delete', 0)->countAllResults(),
        ];

        // Fetch category and subcategory details
        $product['category'] = $categoryModel->find($product['category_id']);
        $product['subcategory'] = $subcategoryModel->find($product['subcategory_id']);

        // Fetch brand details based on brand_id
        $product['brand'] = $brandModel->find($product['brand_id']);

        // Fetch seller details based on seller_id
        $product['seller'] = $sellerModel->select('store_name')->find($product['seller_id']);

        // Pass data to the view
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        $data['product'] = $product;


        $data['similarProducts'] = $productModel->similarProducts($product['subcategory_id']);
        $data['categoryProducts'] = $productModel->categoryProducts($product['category_id']);

        $productRatingsModel = new ProductRatingsModel();

        $data['productRatings'] = $productRatingsModel
            ->select('product_ratings.id, product_ratings.user_id, product_ratings.rate, product_ratings.title, product_ratings.review, product_ratings.created_at, user.name, user.login_type, user.img')
            ->join('user', 'user.id = product_ratings.user_id', 'left')
            ->where([
                'product_ratings.is_approved_to_show' => 1,
                'product_ratings.is_active' => 1,
                'product_ratings.is_delete' => 0,
                'product_ratings.product_id' => $product['id'], // Ensure $productId is sanitized
            ])
            ->findAll();


        if ($user) {
            // Logged-in user: fetch their review and the latest 3 additional reviews
            $loggedInUserRating = $productRatingsModel
                ->select('product_ratings.id, product_ratings.user_id, product_ratings.rate, product_ratings.title, product_ratings.review, product_ratings.created_at, user.name, user.login_type, user.img')
                ->join('user', 'user.id = product_ratings.user_id', 'left')
                ->where([
                    'product_ratings.is_approved_to_show' => 1,
                    'product_ratings.is_active' => 1,
                    'product_ratings.is_delete' => 0,
                    'product_ratings.product_id' => $product['id'],
                    'product_ratings.user_id' => $user['id'],
                ])
                ->first(); // Fetch only the logged-in user's review

            $latestRatings = $productRatingsModel
                ->select('product_ratings.id, product_ratings.user_id, product_ratings.rate, product_ratings.title, product_ratings.review, product_ratings.created_at, user.name, user.login_type, user.img')
                ->join('user', 'user.id = product_ratings.user_id', 'left')
                ->where([
                    'product_ratings.is_approved_to_show' => 1,
                    'product_ratings.is_active' => 1,
                    'product_ratings.is_delete' => 0,
                    'product_ratings.product_id' => $product['id'],
                ])
                ->where('product_ratings.user_id !=', $user['id']) // Exclude the logged-in user's review
                ->orderBy('product_ratings.created_at', 'DESC')
                ->limit(3) // Limit to the latest 3 reviews
                ->findAll();

            $data['productRatings'] = array_filter(array_merge([$loggedInUserRating], $latestRatings));
        } else {
            // Not logged-in: fetch only the latest 3 reviews
            $data['productRatings'] = $productRatingsModel
                ->select('product_ratings.id, product_ratings.user_id, product_ratings.rate, product_ratings.title, product_ratings.review, product_ratings.created_at, user.name, user.login_type, user.img')
                ->join('user', 'user.id = product_ratings.user_id', 'left')
                ->where([
                    'product_ratings.is_approved_to_show' => 1,
                    'product_ratings.is_active' => 1,
                    'product_ratings.is_delete' => 0,
                    'product_ratings.product_id' => $product['id'],
                ])
                ->orderBy('product_ratings.created_at', 'DESC')
                ->limit(3) // Limit to the latest 3 reviews
                ->findAll();
        }

        return view('website/product/productDetails', $data);
    }

    public function getProductWithVariants($product_id)
    {
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();

        $settings = $this->settings;
        $country = $this->country;

        // Fetch product details by product ID
        $product = $productModel->where('id', $product_id)
            ->where('status', 1)->first();

        // If product not found, return an error response
        if (!$product) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Product not found'
            ]);
        }

        // Fetch product variants by product ID
        $variants = $variantModel->where('product_id', $product_id)->where('is_delete', 0)->findAll();

        // Return product and variants as JSON
        return $this->response->setJSON([ 
            'status' => 'success',
            'product' => $product,
            'variants' => $variants,
            'currency_symbol' => $country['currency_symbol'],
            'currency_symbol_position' => $settings['currency_symbol_position'],
        ]);
    }

    public function switchVarient()
    {
        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $user = null;

        // Check if the user is logged in
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        // Default quantity is 0
        $quantity = 0;

        if ($user) {
            // Get input data
            $dataInput = $this->request->getJSON(true);
            $productId = $dataInput['productId'] ?? null;
            $variantId = $dataInput['variantId'] ?? null;

            // Check if both product ID and variant ID are provided
            if ($productId && $variantId) {
                // Look for the specific variant in the user's cart
                $cartItem = $cartsModel
                    ->where('user_id', $user['id'])
                    ->where('product_id', $productId)
                    ->where('product_variant_id', $variantId)
                    ->first();

                // If the variant is in the cart, get the quantity
                if ($cartItem) {
                    $quantity = $cartItem['quantity'];
                }
            }
        }

        // Return the quantity or 0 if not found
        return $this->response->setJSON([
            'status' => 'success',
            'quantity' => $quantity,
            'message' => 'Variant quantity retrieved successfully.',
        ]);
    }

    public function fetchSubcategoryProductList()
    {
        $dataInput = $this->request->getJSON(true);

        $subcategory_slug = $dataInput['subcategory_slug'];

        $settings = $this->settings;
        $country = $this->country;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        $productVariantsModel = new ProductVariantsModel();
        $productModel = new ProductModel();
        $subcategoryModel = new SubcategoryModel();

        $products = []; // Initialize the products array.
        $user = null;

        // Fetch the logged-in user
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        // Fetch cart item count for the user
        $data['cartItemCount'] = $user
            ? $cartsModel->where('user_id', $user['id'])->countAllResults()
            : 0;

        // Fetch sellers based on the user's city
        $sellerModel = new SellerModel();
        $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();

        $subcategory = $subcategoryModel->where('slug', $subcategory_slug)->first();

        foreach ($sellers as $seller) {
            // Apply sorting and filter logic in a single switch statement
            $productQuery = $productModel->where('is_delete', 0)
                ->where('subcategory_id', $subcategory['id'])
                ->where('status', 1)
                ->where('seller_id', $seller['id']);

            // Determine product sorting based on `productSort` value
            switch ($dataInput['productSort']) {
                case 1:
                    // Default sorting without additional order
                    $productQuery->orderBy('row_order', 'ASC');
                    $sellerProducts = $productQuery->findAll();
                    break;

                case 2:
                    // Sort by price (Low to High)
                    $sellerProducts = $productQuery->findAll();
                    foreach ($sellerProducts as &$product) {
                        // Fetch product variants
                        $product['variants'] = $productVariantsModel
                            ->where('product_id', $product['id'])
                            ->where('is_delete', 0)
                            ->findAll();
                    }
                    usort($sellerProducts, function ($a, $b) {
                        $aPrice = isset($a['variants'][0]['discounted_price']) && $a['variants'][0]['discounted_price'] > 0
                            ? $a['variants'][0]['discounted_price']
                            : (isset($a['variants'][0]['price']) ? $a['variants'][0]['price'] : PHP_INT_MAX);

                        $bPrice = isset($b['variants'][0]['discounted_price']) && $b['variants'][0]['discounted_price'] > 0
                            ? $b['variants'][0]['discounted_price']
                            : (isset($b['variants'][0]['price']) ? $b['variants'][0]['price'] : PHP_INT_MAX);

                        return $aPrice <=> $bPrice;
                    });
                    break;

                case 3:
                    // Sort by price (High to Low)
                    $sellerProducts = $productQuery->findAll();
                    foreach ($sellerProducts as &$product) {
                        // Fetch product variants
                        $product['variants'] = $productVariantsModel
                            ->where('product_id', $product['id'])
                            ->where('is_delete', 0)
                            ->findAll();
                    }
                    usort($sellerProducts, function ($a, $b) {
                        $aPrice = isset($a['variants'][0]['discounted_price']) && $a['variants'][0]['discounted_price'] > 0
                            ? $a['variants'][0]['discounted_price']
                            : (isset($a['variants'][0]['price']) ? $a['variants'][0]['price'] : -PHP_INT_MAX);

                        $bPrice = isset($b['variants'][0]['discounted_price']) && $b['variants'][0]['discounted_price'] > 0
                            ? $b['variants'][0]['discounted_price']
                            : (isset($b['variants'][0]['price']) ? $b['variants'][0]['price'] : -PHP_INT_MAX);

                        return $bPrice <=> $aPrice;
                    });
                    break;


                case 4:
                    // Sort by discount (High to Low)
                    $sellerProducts = $productQuery->findAll();
                    foreach ($sellerProducts as &$product) {
                        // Fetch product variants
                        $product['variants'] = $productVariantsModel
                            ->where('product_id', $product['id'])
                            ->where('is_delete', 0)
                            ->findAll();
                    }
                    usort($sellerProducts, function ($a, $b) {
                        // Calculate discount percentage for product A
                        if (isset($a['variants'][0]['price']) && isset($a['variants'][0]['discounted_price']) && $a['variants'][0]['discounted_price'] > 0) {
                            $aDiscountPercentage = (($a['variants'][0]['price'] - $a['variants'][0]['discounted_price']) / $a['variants'][0]['price']) * 100;
                        } else {
                            $aDiscountPercentage = 0; // No discount
                        }

                        // Calculate discount percentage for product B
                        if (isset($b['variants'][0]['price']) && isset($b['variants'][0]['discounted_price']) && $b['variants'][0]['discounted_price'] > 0) {
                            $bDiscountPercentage = (($b['variants'][0]['price'] - $b['variants'][0]['discounted_price']) / $b['variants'][0]['price']) * 100;
                        } else {
                            $bDiscountPercentage = 0; // No discount
                        }

                        // Compare discount percentages in descending order (High to Low)
                        return $bDiscountPercentage <=> $aDiscountPercentage;
                    });
                    break;



                case 5:
                    // Sort by product name ascending
                    $sellerProducts = $productQuery->orderBy('product_name', 'ASC')->findAll();
                    break;

                case 6:
                    // Sort by product popular
                    $sellerProducts = $productQuery->where('popular', 1)->findAll();
                    break;

                case 7:
                    // Sort by product deal of the day
                    $sellerProducts = $productQuery->where('deal_of_the_day', 1)->findAll();
                    break;

                // Add other sorting cases if needed
                default:
                    $sellerProducts = $productQuery->findAll();
                    break;
            }

            foreach ($sellerProducts as $product) {
                // 2) Fetch non-deleted variants for this product
                $variants = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0)
                    ->findAll();

                // 3) Only proceed if we actually got at least one variant
                if (empty($variants)) {
                    // no variants → skip this product
                    continue;
                }

                // 4) Attach the variants array
                $product['variants'] = $variants;

                // 5) Default cart_quantity to zero
                $product['cart_quantity'] = 0;

                // 6) If the user is logged in, see if they have this in their cart
                if (isset($user['id'])) {
                    $cartItem = $cartsModel
                        ->where('user_id', $user['id'])
                        ->where('product_id', $product['id'])
                        ->first();
                    if ($cartItem) {
                        $product['cart_quantity'] = (int) $cartItem['quantity'];
                    }
                }

                // 7) Finally, push into your output array
                $products[] = $product;
            }
        }

        // Return response with sorted products
        return $this->response->setJSON([
            'status' => 'success',
            'products' => $products,
            'base_url' => base_url(),
            'currency_symbol' => $country['currency_symbol'],
            'currency_symbol_position' => $settings['currency_symbol_position'],
        ]);
    }

    public function writeReview()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $dataInput = $this->request->getJSON(true);
        $user = null;

        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'To write a review, please log in and ensure you have purchased the item.',
            ]);
        }

        $orderProductModel = new OrderProductModel();
        $orderProducts = $orderProductModel->where('product_id', $dataInput['productId'])->where('user_id', $user['id'])->first();
        if (!$orderProducts) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'To write a review, ensure you have purchased the item.',
            ]);
        }

        $productRatingsModel = new ProductRatingsModel();
        $productRating = $productRatingsModel->where('product_id', $dataInput['productId'])->where('user_id', $user['id'])->first();

        if ($productRating) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You already write review for this product',
            ]);
        }


        $is_approved_to_show = 0;
        if ($this->settings['auto_review_approval']) {
            $is_approved_to_show = 1;
        }


        $productRatingsData = [
            'product_id' => $dataInput['productId'],
            'user_id' => $user['id'],
            'rate' => $dataInput['rate'],
            'title' => $dataInput['title'],
            'review' => $dataInput['review'],
            'created_at' => date('Y-m-d H:i:s'),
            'is_approved_to_show' => $is_approved_to_show,
            'is_active' => 1,
            'is_delete' => 0
        ];

        $productRatings = $productRatingsModel->insert($productRatingsData);

        if ($productRatings) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'You write Review successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unable to write Review']);
        }
    }
}
