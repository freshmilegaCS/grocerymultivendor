<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AIReportDataModel;
use App\Models\CountryModel;
use App\Models\OrderModel;
use App\Models\OrderProductModel;
use App\Models\OrderReturnRequestModel;
use App\Models\SettingsModel;

class Report extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {

            if (!can_view('order-report-ai')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }

            $settingModel = new SettingsModel();
            $appSetting = $settingModel->getSettings();


            return view('report/aiInsight', [
                'settings' => $appSetting,
            ]);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function generateOrderInsights()
    {
        $output = ['success' => false];

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('order-report-ai')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        

        $orderModel = new OrderModel();
        $orderProductModel = new OrderProductModel();
        $orderReturnModel = new OrderReturnRequestModel();
        $aiReportDataModel = new AIReportDataModel();
        $aiReport = $aiReportDataModel->orderBy('id', 'desc')->first();

        if (!$aiReport) {
            $aiReport['from_date'] = date('Y-m-01');
            $aiReport['to_date'] = date('Y-m-t');
        }

        // Total Orders
        $totalOrders = $orderModel->where('order_date >=', $aiReport['from_date'])->where('order_date <=', $aiReport['to_date'])->countAll();

        // Total Revenue
        $totalRevenue = $orderModel->selectSum('subtotal')->where('order_date >=', $aiReport['from_date'])->where('order_date <=', $aiReport['to_date'])->first()['subtotal'];

        // Total Discounts Given
        $totalDiscounts = $orderModel->selectSum('coupon_amount')->where('order_date >=', $aiReport['from_date'])->where('order_date <=', $aiReport['to_date'])->first()['coupon_amount'];

        // Total Refunds Processed
        $totalRefunds = $orderReturnModel->where('status', 5)->where('created_at >=', $aiReport['from_date'])->where('created_at <=', $aiReport['to_date'])->countAllResults();

        // Top Selling Products
        $topProducts = $orderProductModel
            ->select('order_products.product_name, SUM(order_products.quantity) as total_sold')
            ->join('orders', 'orders.id = order_products.order_id',  'left')
            ->groupBy('order_products.product_id')
            ->orderBy('total_sold', 'DESC')
            ->where('orders.created_at >=', $aiReport['from_date'])
            ->where('orders.created_at <=', $aiReport['to_date'])
            ->limit(5)
            ->findAll();

        // Orders by Payment Method
        $paymentMethods = $orderModel->select('payment_method.title as payment_method_title , COUNT(orders.id) as total_orders');

        $paymentMethods->where('orders.order_date >=', $aiReport['from_date']);
        $paymentMethods->where('orders.order_date <=', $aiReport['to_date']);

        $paymentMethods->join('payment_method', 'payment_method.id = orders.payment_method_id');
        $paymentMethods->groupBy('orders.payment_method_id');
        $paymentMethods = $paymentMethods->get()->getResult();  // Fix findAll() issue

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();

        // Prepare ChatGPT prompt
        $prompt = "Generate a detailed insight report based on the following order data:\n\n"
            . "Total Orders: $totalOrders\n"
            . "Total Revenue: " . $country['currency_symbol'] . number_format($totalRevenue, 2) . "\n"
            . "Total Discounts Given: " . $country['currency_symbol'] . number_format($totalDiscounts, 2) . "\n"
            . "Total Refunds Processed: $totalRefunds\n"
            . "Top Selling Products: " . json_encode($topProducts) . "\n"
            . "Orders by Payment Methods: " . json_encode($paymentMethods) . "\n\n"
            . "Provide an analytical summary including trends and business recommendations.";

        // Get AI-generated insights

        if ($aiReport['id']) {
            $insights = $aiReport['ai_insight'];
        } else {
            $insights = $this->askChatGPT($prompt, $aiReport['from_date'], $aiReport['to_date']);
        }


        return $this->response->setJSON([
            'order_summary' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_discounts' => $totalDiscounts,
                'total_refunds' => $totalRefunds,
                'top_products' => $topProducts,
                'payment_methods' => $paymentMethods,
                'country' => $country,
                'report_date' => date('d/M/Y', strtotime($aiReport['from_date'])) . " " . date('d/M/Y', strtotime($aiReport['to_date']))
            ],
            'ai_insights' => $insights
        ]);
    }

    public function refresheOrderInsights()
    {

        if (!session()->has('user_id') || session('account_type') != 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_add('order-report-ai')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }
        

        $orderModel = new OrderModel();
        $orderProductModel = new OrderProductModel();
        $orderReturnModel = new OrderReturnRequestModel();
        $from_date = $this->request->getPost('from_date');
        $to_date = $this->request->getPost('to_date');

        // Total Orders
        $totalOrders = $orderModel->where('order_date >=', $from_date)->where('order_date <=', $to_date)->countAll();

        // Total Revenue
        $totalRevenue = $orderModel->selectSum('subtotal')->where('order_date >=', $from_date)->where('order_date <=', $to_date)->first()['subtotal'];

        // Total Discounts Given
        $totalDiscounts = $orderModel->selectSum('coupon_amount')->where('order_date >=', $from_date)->where('order_date <=', $to_date)->first()['coupon_amount'];

        // Total Refunds Processed
        $totalRefunds = $orderReturnModel->where('status', 5)->where('created_at >=', $from_date)->where('created_at <=', $to_date)->countAllResults();

        // Top Selling Products
        $topProducts = $orderProductModel
            ->select('order_products.product_name, SUM(order_products.quantity) as total_sold')
            ->join('orders', 'orders.id = order_products.order_id',  'left')
            ->groupBy('order_products.product_id')
            ->orderBy('total_sold', 'DESC')
            ->where('orders.created_at >=', $from_date)
            ->where('orders.created_at <=', $to_date)
            ->limit(5)
            ->findAll();

        // Orders by Payment Method
        $paymentMethods = $orderModel
            ->select('payment_method.title as payment_method_title , COUNT(orders.id) as total_orders')
            ->where('orders.order_date >=', $from_date)
            ->where('orders.order_date <=', $to_date)
            ->join('payment_method', 'payment_method.id = orders.payment_method_id')
            ->groupBy('orders.payment_method_id')
            ->findAll();

        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();

        // Prepare ChatGPT prompt
        $prompt = "Generate a detailed insight report based on the following order data:\n\n"
            . "Total Orders: $totalOrders\n"
            . "Total Revenue: " . $country['currency_symbol'] . number_format($totalRevenue, 2) . "\n"
            . "Total Discounts Given: " . $country['currency_symbol'] . number_format($totalDiscounts, 2) . "\n"
            . "Total Refunds Processed: $totalRefunds\n"
            . "Top Selling Products: " . json_encode($topProducts) . "\n"
            . "Orders by Payment Methods: " . json_encode($paymentMethods) . "\n\n"
            . "Provide an analytical summary including trends and business recommendations.";

        // Get AI-generated insights
        $insights = $this->askChatGPT($prompt, $from_date, $to_date);


        return $this->response->setJSON([
            'order_summary' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_discounts' => $totalDiscounts,
                'total_refunds' => $totalRefunds,
                'top_products' => $topProducts,
                'payment_methods' => $paymentMethods,
                'country' => $country,
                'report_date' => date('d/M/Y', strtotime($from_date)) . " " . date('d/M/Y', strtotime($to_date))

            ],
            'ai_insights' => $insights
        ]);
    }

    private function askChatGPT($prompt, $from_date, $to_date)
    {
        $settingModel = new SettingsModel();
        $appSetting = $settingModel->getSettings();

        $apiUrl = 'https://api.openai.com/v1/chat/completions';
        $aiReportDataModel = new AIReportDataModel();

        $data = [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are an expert e-commerce analyst. Format the response in well-structured, beautiful HTML using Bootstrap classes from AdminLTE v3 for styling. 
        
        - Use **strong headers**, **subheadings**, and **bullet points** for clarity.  
        - Highlight key **business insights**, **recommendations**, and **ways to reduce return rates**.  
        - Avoid adding **external links** or including **external CSS files**, as we are already imported css."
                ],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7
        ];


        $headers = [
            'Authorization: Bearer ' . $appSetting['chatgpt_api_key'],
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        if (isset($responseData['error']['code'])) {
            return $responseData['error']['message'];
        }


        if (isset($responseData['choices']) && $responseData['choices'][0]['message']['content']) {
            $cleanedInsight = $this->cleanHtmlContent($responseData['choices'][0]['message']['content']);
            $data = ['from_date' => $from_date, 'to_date' => $to_date, 'created_at' => date("Y-m-d H:i:s"), 'ai_insight' => $cleanedInsight];
            $db = \Config\Database::connect();
            $db->reconnect();
            $aiReportDataModel->insert($data);
            return $responseData['choices'][0]['message']['content'];
        }
        return 'Error generating insights';
    }
    private function cleanHtmlContent($html)
    {
        // Match the first opening tag and capture everything inside it
        if (preg_match('/(<[a-zA-Z][^>]*>)(.*)(<\/\1>)/s', $html, $matches)) {
            return $matches[0]; // Return only the main HTML content
        }
        return $html; // If no match, return original content
    }
}
