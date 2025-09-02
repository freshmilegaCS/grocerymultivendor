<?php

namespace App\Controllers\Seller; 
use App\Controllers\BaseController; 

use App\Models\OrderModel;
use App\Models\CategoryModel;
use App\Models\FeedbackModel;
use App\Models\ProductModel;
use App\Models\SellerModel;
use App\Models\SettingsModel;
use App\Models\SubcategoryModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller' ) {
            $settingModel = new SettingsModel();
            $appSetting = $settingModel->getSettings();
            $userModel = new UserModel();
            $categoryModel = new CategoryModel();
            $subcategoryModel = new SubcategoryModel();
            $productModel = new ProductModel();
            $orderModel = new OrderModel();
            $feedbackModel = new FeedbackModel();
            $sellerModel = new SellerModel();

            $orderCounts  = $orderModel->getDailyOrderCountForCurrentMonthForSeller();
            $orderCountsMonthWise = $orderModel->getMonthlyOrderCountForCurrentYearForSeller();
            $categories = [];
            $data = [];

            foreach ($orderCounts as $orderCount) {
                $categories[] = $orderCount['day'];
                $data[] = $orderCount['order_count'];
            }

            foreach ($orderCountsMonthWise as $orderCount) {
                $categoriesMonthWise[] = $orderCount['month'];
                $dataMonthWise[] = $orderCount['order_count'];
            }
            // Fetch data using models
            $totalUsers = $userModel->getTotalUsers();
            $totalCategories = $categoryModel->getTotalCategoriesForSeller();
            $totalSubcategories = $subcategoryModel->getTotalSubcategoriesForSeller();
            $totalProducts = $productModel->getTotalProductsForSeller();
            $totalOrders = $orderModel->getTotalOrdersForSeller();
            $pendingOrders = $orderModel->getOrdersByStatusForSeller(1);
            $shippedOrders = $orderModel->getOrdersByStatusForSeller(2);
            $deliveredOrders = $orderModel->getOrdersByStatusForSeller(3);
            $totalFeedbacks = $feedbackModel->getTotalFeedbacks();
            $lowStockCount = $productModel->countLowStockProductsForSeller(10); // Define your threshold
            $outOfStockCount = $productModel->countOutOfStockProductsForSeller();
            $sellerInfo = $sellerModel->select('view_customer_details')->where('id', session()->get('user_id'))->where('is_delete', 0)->where('status', 1)->first();

            $data = [
                'categories' => json_encode($categories),
                'data' => json_encode($data),
                'categoriesMonthWise' => json_encode($categoriesMonthWise),
                'dataMonthWise' => json_encode($dataMonthWise),
                'totalUsers' => $totalUsers,
                'totalCategories' => $totalCategories,
                'totalSubcategories' => $totalSubcategories,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'shippedOrders' => $shippedOrders,
                'deliveredOrders' => $deliveredOrders,
                'totalFeedbacks' => $totalFeedbacks,
                'settings' => $appSetting,
                'lowStockCount' => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
                'sellerInfo' =>$sellerInfo
            ];

            return view('sellerPanel/dashboard', $data);
        }else {
            return redirect()->to('seller/auth/login');
        }
    }
}
