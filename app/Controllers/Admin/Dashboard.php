<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\OrderModel;
use App\Models\CategoryModel;
use App\Models\FeedbackModel;
use App\Models\ProductModel;
use App\Models\SettingsModel;
use App\Models\SubcategoryModel;
use App\Models\UserModel;
use App\Models\CountryModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id')  && session('account_type') == 'Admin') {
            $userModel = new UserModel();
            $categoryModel = new CategoryModel();
            $subcategoryModel = new SubcategoryModel();
            $productModel = new ProductModel();
            $orderModel = new OrderModel();
            $feedbackModel = new FeedbackModel();
            $countryModel = new CountryModel();
            $country = $countryModel->where('is_active', 1)->first();
            $orderCounts  = $orderModel->getDailyOrderCountForCurrentMonth();
            $orderCountsMonthWise = $orderModel->getMonthlyOrderCountForCurrentYear();
            $categories = [];

            foreach ($orderCounts as $orderCount) {
                $categories[] = $orderCount['day'];
                $monthWiseData[] = $orderCount['order_count'];
            }

            foreach ($orderCountsMonthWise as $orderCount) {
                $categoriesMonthWise[] = $orderCount['month'];
                $dataMonthWise[] = $orderCount['order_count'];
            }
            // Fetch data using models
            $settingModel = new SettingsModel();
            $settings = $settingModel->getSettings();

            $totalUsers = $userModel->getTotalUsers();
            $totalCategories = $categoryModel->getTotalCategories();
            $totalSubcategories = $subcategoryModel->getTotalSubcategories();
            $totalProducts = $productModel->getTotalProducts();
            $totalOrders = $orderModel->getTotalOrders();
            $pendingOrders = $orderModel->getOrdersByStatus(1);
            $shippedOrders = $orderModel->getOrdersByStatus(2);
            $deliveredOrders = $orderModel->getOrdersByStatus(3);
            $totalFeedbacks = $feedbackModel->getTotalFeedbacks();
            $lowStockCount = $productModel->countLowStockProducts(10); // Define your threshold
            $outOfStockCount = $productModel->countOutOfStockProducts();

            $today = date('Y-m-d');
            $lastMonth = date('Y-m-d', strtotime('-56 days')); // Last 8 weeks

            // Get total sales for today
            $todaySales = $orderModel->select('SUM(subtotal) as total_sales')
                ->where('DATE(created_at)', $today)
                ->first();

            // Get total sales for the same day last month
            $lastMonthSales = $orderModel->select('SUM(subtotal) as total_sales')
                ->where('DATE(created_at)', date('Y-m-d', strtotime('-4 weeks', strtotime($today))))
                ->first();

            $todaySalesAmount = $todaySales['total_sales'] ?? 0;
            $lastMonthSalesAmount = $lastMonthSales['total_sales'] ?? 0;

            // Calculate increase or decrease
            $salesDifference = $todaySalesAmount - $lastMonthSalesAmount;
            $isIncrease = $salesDifference > 0;
            $salesPercentage = $lastMonthSalesAmount > 0
                ? round(($salesDifference / $lastMonthSalesAmount) * 100, 2)
                : 100;

            // Fetch weekly sales data for this month and last month
            $weeklySales = $orderModel->select('YEARWEEK(created_at, 1) as week, SUM(subtotal) as total_sales')
                ->where('created_at >=', $lastMonth)
                ->groupBy('week')
                ->orderBy('week', 'ASC')
                ->findAll();

            // Generate all weeks dynamically for the last 8 weeks
            $weeks = [];
            $totalsThisMonth = [];
            $totalsLastMonth = [];

            for ($i = 0; $i < 8; $i++) {
                $weekNumber = date('W', strtotime("-$i week"));
                $weeks[$weekNumber] = 'Week ' . $weekNumber;
                if ($i < 4) {
                    $totalsThisMonth[$weekNumber] = 0; // Default to 0 sales for this month
                } else {
                    $totalsLastMonth[$weekNumber] = 0; // Default to 0 sales for last month
                }
            }

            // Map fetched sales data to respective weeks
            foreach ($weeklySales as $data) {
                $weekNum = substr($data['week'], -2);
                if (isset($weeks[$weekNum])) {
                    if ($weekNum >= date('W', strtotime('-4 weeks'))) {
                        $totalsThisMonth[$weekNum] = (float) $data['total_sales'];
                    } else {
                        $totalsLastMonth[$weekNum] = (float) $data['total_sales'];
                    }
                }
            }
            $salesByLocation = $orderModel->getSalesByLocation();
            $averageOrderValue = round($orderModel->getAverageOrderValue(), 2);
            $data = [
                'categories' => json_encode($categories),
                'data' => json_encode($monthWiseData),
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
                'settings' => $settings,
                'lowStockCount' => $lowStockCount,
                'outOfStockCount' => $outOfStockCount,
                'weeks' => json_encode(array_values($weeks)),
                'totalsThisMonth' => json_encode(array_values($totalsThisMonth)),
                'totalsLastMonth' => json_encode(array_values($totalsLastMonth)),
                'total_sales_today' => $todaySalesAmount,
                'sales_difference' => abs($salesDifference),
                'sales_percentage' => $salesPercentage,
                'isIncrease' => $isIncrease,
                'country' => $country,
                'salesByLocation' => $salesByLocation,
                'averageOrderValue' => $averageOrderValue

            ];

            return view('dashboard', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
}
