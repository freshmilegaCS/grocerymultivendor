<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\BannerModel;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\BrandModel;
use App\Models\CartsModel;
use App\Models\FaqsModel;
use App\Models\HomeSectionModel;
use App\Models\ProductVariantsModel;
use App\Models\SellerModel;
use App\Models\UserModel;
use App\Models\SubcategoryModel;
use App\Models\HighlightsModel;


class Home extends BaseController
{
    public function index()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();

        $user = null;
        // Fetch user details
        $loginType = session()->get('login_type');
        if ($loginType == 'email') {
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        } elseif ($loginType == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        $data['cartItemCount'] = $user ? $cartsModel->where('user_id', $user['id'])->countAllResults() : 0;

        // Redirect to loader if no city is selected
        if (session()->get('city_id') == null) {
            $data['session_load'] = 0;
            return view('website/loader', $data);
        }

        $cityId = session()->get('city_id');
        $homeSectionModel = new HomeSectionModel();
        $productVariantsModel = new ProductVariantsModel();
        $sellerModel = new SellerModel();
        $bannerModel = new BannerModel();
        $categoryModel = new CategoryModel();
        $subcategoryModel = new SubcategoryModel();
        $productModel = new ProductModel();
        $brandModel = new BrandModel();

        // Fetch sellers for the selected city
        $sellers = $sellerModel->where('city_id', $cityId)->findColumn('id');
        if ($sellers === null) {
            return view('website/comingSoonCity', $data);
        }

        // Fetch banners
        $data['headerBanner'] = $bannerModel->getActiveBannerForApp(0);
        $data['dealOftheDayBanner'] = $bannerModel->getActiveBannerForApp(1);
        $data['homeBanner'] = $bannerModel->getActiveBannerForApp(2);
        $data['footerBanner'] = $bannerModel->getActiveBannerForApp(3);

        $data['categories'] = $this->settings['frontend_category_section']
            ? $categoryModel->orderBy('row_order', 'ASC')->findAll()
            : [];

        foreach ($data['categories'] as $i => $category) {
            $subcategory = $subcategoryModel->where('category_id', $category['id'])
                ->orderBy('row_order', 'ASC')
                ->first();
            $data['categories'][$i]['firstSubcategory'] = $subcategory;
        }



        // Fetch popular products, brands, and deal of the day products
        $data['popularProducts'] = $this->settings['frontend_popular_section'] ? $productModel->getAllPopularProduct() : [];
        $data['brands'] = $this->settings['frontend_brand_section'] ? $brandModel->getBrandList() : [];
        $data['dealOfTheDayProducts'] = $this->settings['frontend_deal_of_the_day_section'] ? $productModel->getAllDealOfTheDayProduct() : [];
        $data['sellers'] = $this->settings['frontend_seller_section']
            ? $sellerModel->where('city_id', $cityId)->where('is_delete', 0)->where('status', 1)->findAll()
            : [];

        $highlightsModel = new HighlightsModel();
        $sellers1 = $sellerModel
            ->where('city_id', $cityId)
            ->where('is_delete', 0)
            ->where('status', 1)
            ->findAll();

        $sellerIds = array_column($sellers1, 'id');
        $highlightsData = [];
        if (!empty($sellerIds)) {
            $highlights = $highlightsModel
                ->where('is_active', 1)
                ->whereIn('seller_id', $sellerIds)
                ->findAll();
            $sellerSlugs = [];
            foreach ($sellers1 as $seller) {
                $sellerSlugs[$seller['id']] = $seller['slug'];
            }
            foreach ($highlights as $highlight) {
                $sellerId = $highlight['seller_id'];
                $slug = $sellerSlugs[$sellerId] ?? '';

                $highlightsData[] = [
                    'title'       => $highlight['title'],
                    'description' => $highlight['description'],
                    'video'       => $highlight['video'],
                    'image'       => $highlight['image'],
                    'seller_slug' => $slug,
                ];
            }
        }
        $data['highlights'] = $highlightsData;


        // Load bestseller categories
        $bestsellerCategories = $categoryModel->where('is_bestseller_category', 1)->findAll();
        $bestsellerCategoriesResult = [];
        foreach ($bestsellerCategories as $category) {
            // Get first 4 product images for this category
            $products = $productModel->select('main_img')
                ->where('category_id', $category['id'])
                ->limit(4)
                ->find();
            // Total product count
            $totalProducts = $productModel->where('category_id', $category['id'])->countAllResults();
            // Format images with base URL
            $productImages = [];
            foreach ($products as $product) {
                $imgPath = $product['main_img'];
                $productImages[] = !empty($imgPath) ? base_url($imgPath) : base_url('assets/images/no-image.png');
            }
            $firstSubcategory = $subcategoryModel
                ->where('category_id', $category['id'])
                ->orderBy('row_order', 'ASC')
                ->first();

            $bestsellerCategoriesResult[] = [
                'category_id'   => $category['id'],
                'category_name' => $category['category_name'],
                'images'        => $productImages,
                'total_count'   => $totalProducts,
                'firstSubcategory' => $firstSubcategory
            ];
        }
        $data['allBestsellerCategory'] = $bestsellerCategoriesResult;


        // // Fetch home sections and their respective products
        // $homes = $homeSectionModel->where('is_active', 1)->findAll();
        // $homeSections = [];

        // // Add user details if logged in
        // $data['user'] = (session()->has('email') || session()->has('mobile')) ? $user : [];

        // foreach ($homes as $home) {
        //     $productModel
        //         ->where('category_id', $home['category_id'])
        //         ->where('subcategory_id', $home['subcategory_id']);

        //     if ($sellers !== null) {
        //         $productModel->whereIn('seller_id', $sellers);
        //     } else {
        //         return view('website/comingSoonCity', $data);
        //     }

        //     $products = $productModel
        //         ->where('is_delete', 0)
        //         ->where('status', 1)
        //         ->findAll();

        //     foreach ($products as &$product) {
        //         $product['variants'] = $productVariantsModel
        //             ->where('product_id', $product['id'])
        //             ->where('is_delete', 0)
        //             ->findAll();

        //         $product['cart_quantity'] = 0;
        //         if ($user) {
        //             $cartItem = $cartsModel
        //                 ->where('user_id', $user['id'])
        //                 ->where('product_id', $product['id'])
        //                 ->first();
        //             $product['cart_quantity'] = $cartItem ? $cartItem['quantity'] : 0;
        //         }
        //     }

        //     $home['section'] = $products;
        //     $homeSections[] = $home;
        // }

        // $data['homeSectionProducts'] = $homeSections;

        // Fetch home sections and their respective products
        $homes = $homeSectionModel->where('is_active', 1)->findAll();
        $homeSections = [];

        // Add user details if logged in
        $data['user'] = (session()->has('email') || session()->has('mobile')) ? $user : [];

        $homeSections = [];

        foreach ($homes as $home) {
            $productModel
                ->where('category_id', $home['category_id'])
                ->where('subcategory_id', $home['subcategory_id']);

            if ($sellers !== null) {
                $productModel->whereIn('seller_id', $sellers);
            } else {
                return view('website/comingSoonCity', $data);
            }

            $sellerProducts = $productModel
                ->where('is_delete', 0)
                ->where('status', 1)
                ->findAll();

            // ✅ Skip this home section if no products found
            if (empty($sellerProducts)) {
                continue;
            }

            $products = []; // ✅ Initialize for each home section

            foreach ($sellerProducts as $product) {
                $variants = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0)
                    ->findAll();

                // ✅ Skip product if no variants found
                if (empty($variants)) {
                    continue;
                }

                $product['variants'] = $variants;

                $product['cart_quantity'] = 0;
                if ($user) {
                    $cartItem = $cartsModel
                        ->where('user_id', $user['id'])
                        ->where('product_id', $product['id'])
                        ->first();
                    $product['cart_quantity'] = $cartItem ? $cartItem['quantity'] : 0;
                }

                $products[] = $product;
            }

            // ✅ Only include home section if it has products with variants
            if (!empty($products)) {
                $home['section'] = $products;
                $homeSections[] = $home;
            }
        }

        $data['homeSectionProducts'] = $homeSections;





        return view('website/home/home', $data);
    }


    public function contactUs()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        return view('website/home/contactUs', $data);
    }

    public function faq()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $faqsModel = new FaqsModel();

        $data['faqs'] = $faqsModel->where('status', 1)->orderBy('row_order', 'asc')->findAll();

        return view('website/home/faq', $data);
    }

    public function aboutUs()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $data['aboutUs'] = $this->settings['customer_app_about'];


        return view('website/home/aboutUs', $data);
    }

    public function privacyPolicy()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $data['privacyPolicy'] = $this->settings['customer_app_privacy_policy'];

        return view('website/home/privacyPolicy', $data);
    }

    public function termsCondition()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $data['termsCondition'] = $this->settings['customer_app_terms_policy'];

        return view('website/home/termsCondition', $data);
    }

    public function refundPolicy()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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
        $data['refundPolicy'] = $this->settings['customer_app_refund_policy'];


        return view('website/home/refundPolicy', $data);
    }

    public function noProductAvilable()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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


        return view('website/category/noProductAvilable', $data);
    }

    public function privacyPolicyDelivery()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $data['privacyPolicy'] = $this->settings['delivery_app_privacy_policy'];

        return view('website/home/privacyPolicy', $data);
    }

    public function termsConditionDelivery()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $data['termsCondition'] = $this->settings['delivery_app_terms_policy'];

        return view('website/home/termsCondition', $data);
    }

    public function aboutUsDelivery()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

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

        $data['aboutUs'] = $this->settings['delivery_app_about'];


        return view('website/home/aboutUs', $data);
    }
}
