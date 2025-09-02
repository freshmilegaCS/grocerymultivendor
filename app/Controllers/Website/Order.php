<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\AddressModel;
use App\Models\CartsModel;
use App\Models\ProductModel;
use App\Models\ProductVariantsModel;
use App\Models\TaxModel;
use App\Models\UsedCouponModel;
use App\Models\UserModel;

use App\Libraries\GeoUtils;
use App\Libraries\CartSummery;
use App\Models\CouponModel;
use App\Models\OrderModel;
use App\Models\OrderProductModel;
use App\Models\OrderReturnRequestModel;
use App\Models\OrderStatusesModel;
use App\Models\OrderStatusListsModel;
use App\Models\WalletModel;
use App\Models\PaymentMethodModel;

use Razorpay\Api\Api;
use ReflectionClass;

use Cashfree\Cashfree; // Main Cashfree class

header('Content-Type: text/html; charset=utf-8');

use Dompdf\Dompdf;
use Dompdf\Options;

class Order extends BaseController
{
    protected $razorpayApiKey;
    protected $razorpayApiSecret;

    protected $paypalApiKey;
    protected $paypalApiSecret;

    protected $paystackApiKey;
    protected $paystackApiSecret;

    protected $cashFreeApiKey;
    protected $cashFreeApiSecret;

    public function __construct()
    {
        $paymentMethodModel = new PaymentMethodModel();

        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 2)->first();
        if (isset($paymentMethods)) {

            $this->razorpayApiKey = $paymentMethods['api_key'];
            $this->razorpayApiSecret = $paymentMethods['secret_key'];
        } else {
            $this->razorpayApiKey = "";
            $this->razorpayApiSecret = "";
        }
        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 3)->first();
        if (isset($paymentMethods)) {
            $this->paypalApiKey = $paymentMethods['api_key'];
            $this->paypalApiSecret = $paymentMethods['secret_key'];
        } else {
            $this->paypalApiKey = "";
            $this->paypalApiSecret = "";
        }
        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 4)->first();
        if (isset($paymentMethods)) {
            $this->paystackApiKey = $paymentMethods['api_key'];
            $this->paystackApiSecret = $paymentMethods['secret_key'];
        } else {
            $this->paystackApiKey = "";
            $this->paystackApiSecret = "";
        }
        $paymentMethods =  $paymentMethodModel->where('status', 1)->where('id', 5)->first();
        if (isset($paymentMethods)) {
            $this->cashFreeApiKey = $paymentMethods['api_key'];
            $this->cashFreeApiSecret = $paymentMethods['secret_key'];
        } else {
            $this->cashFreeApiKey = "";
            $this->cashFreeApiSecret = "";
        }
    }

    public function orderHistory()
    {
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();

        $user = null;

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

        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];

        $orderModel = new OrderModel();
        $orders = $orderModel->where('user_id', $user['id'])
            ->orderBy('id', 'DESC')
            ->findAll();

        $orderStatusListsModel = new OrderStatusListsModel();
        $orderStatuses = $orderStatusListsModel->findAll();

        // Build the statusMap with 'id' as the key
        $statusMap = [];
        foreach ($orderStatuses as $status) {
            $statusMap[$status['id']] = [
                'status' => $status['status'],
                'text_color' => $status['text_color'],
                'bg_color' => $status['bg_color'],
            ];
        }

        $orderProductModel = new OrderProductModel();


        // Add status details to each order
        foreach ($orders as &$order) {
            $statusId = $order['status']; // Ensure this matches the 'id' in the status table
            if (isset($statusMap[$statusId])) {
                $order['status_name'] = $statusMap[$statusId]['status'];
                $order['text_color'] = $statusMap[$statusId]['text_color'];
                $order['bg_color'] = $statusMap[$statusId]['bg_color'];
            } else {
                $order['status_name'] = 'Unknown';
                $order['text_color'] = 'text-gray-500'; // Default text color
                $order['bg_color'] = 'bg-gray-200'; // Default background color
            }

            $order['orderProducts'] = $orderProductModel
                ->select('order_products.*')
                ->join('order_return_request', 'order_return_request.order_products_id = order_products.id AND order_return_request.status = 4', 'left')
                ->where('order_products.order_id', $order['id'])
                ->where('order_return_request.order_products_id IS NULL')
                ->findAll();
        }

        $data['orders'] = $orders;


        $data['additional_charge_status'] = $this->settings['additional_charge_status'];
        $data['additional_charge_name'] = $this->settings['additional_charge_name'];
        $data['additional_charge'] = $this->settings['additional_charge'];

        $data['totalProductPrice'] = 0;
        $data['totalProductTax'] = 0;


        return view('website/order/orderHistory', $data);
    }


    public function orderDetails($order_id)
    {
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }
        $user = null;
        date_default_timezone_set($this->timeZone['timezone']);

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
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

        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];

        $orderModel = new OrderModel();
        $order = $orderModel->where('id', $order_id)->where('user_id', $user['id'] )->first(); // preferred over ->where('id', ...)->first()

        if (!isset($order['id'])){
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found.']);
        }
        if (!empty($order) && $order['coupon_id'] > 0) {
            $couponModel = new CouponModel();
            $coupon = $couponModel->find($order['coupon_id']); // preferred over ->where('id', ...)->first()

            $order['coupon_type'] = $coupon['coupon_type'] ?? 0; // fallback if coupon not found
            $order['coupon_value'] = $coupon['value'] ?? 0; // fallback if coupon not found
        } else {
            $order['coupon_type'] = 0;
            $order['coupon_value'] = 0; // fallback if coupon not found
        }

        $data['order'] = $order;


        $paymentMethodModel = new PaymentMethodModel();
        if ($order['payment_method_id'] == 0) {
            $data['paymentMethod'] = [
                'title' => 'Pay using wallet',
            ];
        } else {
            $data['paymentMethod'] = $paymentMethodModel->where('id', $order['payment_method_id'])->first();
        }

        $orderStatusListsModel = new OrderStatusListsModel();
        $orderStatuses = $orderStatusListsModel->findAll();

        // Build the statusMap with 'id' as the key
        $statusMap = [];
        foreach ($orderStatuses as $status) {
            $statusMap[$status['id']] = [
                'status' => $status['status'],
                'text_color' => $status['text_color'],
                'bg_color' => $status['bg_color'],
            ];
        }

        // Check and map the status details to the order
        if ($order) {
            $statusId = $order['status']; // Assuming 'status' in $order refers to 'id' in status table
            if (isset($statusMap[$statusId])) {
                $order['status_name'] = $statusMap[$statusId]['status'];
                $order['text_color'] = $statusMap[$statusId]['text_color'];
                $order['bg_color'] = $statusMap[$statusId]['bg_color'];
            } else {
                $order['status_name'] = 'Unknown';
                $order['text_color'] = 'text-gray-500'; // Default text color
                $order['bg_color'] = 'bg-gray-200'; // Default background color
            }
        }

        $data['order'] = $order;

        $orderProductModel = new OrderProductModel();
        $productModel = new ProductModel();

        // Fetch all order products
        $orderProducts = $orderProductModel->where('order_id', $order_id)->findAll();

        $orderReturnRequestModel = new OrderReturnRequestModel();
        $existingOrderReturnRequests = $orderReturnRequestModel
            ->where('order_id', $order_id)
            ->groupStart()
            ->where('status', 4)
            ->orWhere('status', 5)
            ->groupEnd()
            ->findAll();

        // Extract the `order_products_id` from the existing requests
        $existingProductIds = array_column($existingOrderReturnRequests, 'order_products_id');

        // Filter $orderProducts to exclude those in $existingProductIds
        $data['orderProducts'] = array_filter($orderProducts, function ($product) use ($existingProductIds) {
            return !in_array($product['id'], $existingProductIds);
        });

        foreach ($data['orderProducts'] as &$orderProduct) {
            $product = $productModel->select('main_img, is_returnable, return_days, id')->find($orderProduct['product_id']);
            $orderProduct['main_img'] = base_url($product['main_img']) ?? null;
            $orderProduct['is_returnable'] = 0;
            $orderProduct['differenceInDays'] = 0;

            $existingRequest = $orderReturnRequestModel
                ->where('order_id', $order['id'])
                ->where('order_products_id', $orderProduct['id'])
                ->first();

            // Convert dates to timestamps
            $orderDeliveryDate = strtotime($order['delivery_date']);
            $currentDate = strtotime(date('Y-m-d'));

            // Calculate difference in days (allowing negative values)
            $differenceInSeconds = $currentDate - $orderDeliveryDate;
            $differenceInDays = floor($differenceInSeconds / (60 * 60 * 24)); // Convert seconds to days

            // Check returnable conditions
            if ($product['is_returnable'] && $differenceInDays <= $product['return_days']) {
                $orderProduct['is_returnable'] = 1;
                $orderProduct['differenceInDays'] = $differenceInDays;
                $orderProduct['order_retuning_status'] = null;

                if ($existingRequest) {
                    $orderProduct['order_retuning_status'] = $existingRequest['status'];
                    $orderProduct['order_retuning_reason'] = $existingRequest['reason'];
                    $orderProduct['order_retuning_remark'] = $existingRequest['remark'];
                    $orderProduct['order_retuning_created_at'] = $existingRequest['created_at'];
                    $orderProduct['order_retuning_updated_at'] = $existingRequest['updated_at'];
                }
            }
        }

        $addressModel = new AddressModel();
        $data['address'] = $addressModel->where('id', $order['address_id'])->first();

        $data['additional_charge_status'] = $this->settings['additional_charge_status'];
        $data['additional_charge_name'] = $this->settings['additional_charge_name'];
        $data['additional_charge'] = $this->settings['additional_charge'];

        // Load the required models
        $orderStatusesModel = new OrderStatusesModel();
        $orderStatusListModel = new OrderStatusListsModel();

        // Fetch all status details from the order_status_lists table
        $statusesList = $orderStatusListModel->whereIn('id', [2, 3, 4, 6])->findAll();

        // Fetch statuses for the given order ID
        $orderStatuses = $orderStatusesModel->where('orders_id', $order_id)->findAll();

        // Map statuses for easier use in the view
        $mappedStatuses = [];
        foreach ($orderStatuses as $status) {
            $mappedStatuses[$status['status']] = [
                'created_at' => $status['created_at'],
                'id' => $status['id']
            ];
        }

        // Prepare the data to be sent to the view
        $data['orderStatuses'] = [];
        foreach ($statusesList as $status) {
            $data['orderStatuses'][] = [
                'id' => $status['id'],
                'name' => $status['status'],
                'color' => $status['color'],
                'text_color' => $status['text_color'],
                'bg_color' => $status['bg_color'],
                'is_active' => isset($mappedStatuses[$status['id']]),
                'created_at' => $mappedStatuses[$status['id']]['created_at'] ?? null,
            ];
        }

        $data['is_order_cancelleble'] = 0;

        $order_cancelled_till = $this->settings['order_cancelled_till'];
        if ($order['status'] <= $order_cancelled_till) {
            $data['is_order_cancelleble'] = 1;
        }

        // Fetch all return requests with status = 4 for the given order ID
        $orderReturnRequest = $orderReturnRequestModel
            ->where('order_id', $order_id)
            ->groupStart()
            ->where('status', 4)
            ->orWhere('status', 5)
            ->groupEnd()
            ->findAll();

        $data['returned_item_list'] = [];

        // Loop through the returned items and fetch corresponding product details
        foreach ($orderReturnRequest as $returnItem) {
            $returnedProduct = $orderProductModel->select('product_name, product_variant_name, quantity, price, discounted_price, tax_amount, product_id')->where('id', $returnItem['order_products_id'])->first();

            if ($returnedProduct) {
                $productDetails = $productModel->select('main_img')->where('id', $returnedProduct['product_id'])->first();

                if ($productDetails) {
                    $data['returned_item_list'][] = [
                        'return_request' => $returnItem,
                        'order_product' => $returnedProduct,
                        'product' => $productDetails,
                    ];
                }
            }
        }

        return view('website/order/orderDetails', $data);
    }

    public function cancelOrder()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $data = $this->request->getJSON(true);


        $order_cancelled_till = $this->settings['order_cancelled_till'];

        $orderModel = new OrderModel();
        $order_id = $data['order_id'];
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not authorized.']);
        }
        
        $order = $orderModel->where('id', $order_id)->where('user_id', $user['id'] )->first(); // preferred over ->where('id', ...)->first()

        if (!isset($order['id'])){
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found.']);
        }



        // Check if order can be cancelled
        if ($order['status'] <= $order_cancelled_till) {
            // Update order status and note
            $updateData = [
                'note' => $data['note'],
                'status' => 7
            ];

            $orderModel->update($data['order_id'], $updateData);

            // Insert into order statuses
            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id'  => $data['order_id'],
                'status'     => 7,
                'created_by' => $user['id'],
                'user_type'  => 'Customer',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $orderStatusesModel->insert($orderStatusesData);

            if ($order['payment_method_id'] != 1) {
                $grandTotal = $order['subtotal'] + $order['tax'] + $order['delivery_charge'] + $order['additional_charge'] + $order['used_wallet_amount'];

                $walletModel = new WalletModel();
                $wallet = $walletModel->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC')
                    ->first();

                $totalWalletAmount = $wallet['closing_amount'] + $grandTotal;

                $walletData  = [
                    'user_id' => $user['id'],
                    'amount' => $wallet['closing_amount'],
                    'closing_amount' => $totalWalletAmount,
                    'flag' => 'credit',
                    'remark' => 'Cancelled Order amount added, Order Id : ' . $order['order_id'],
                    'date' => date('Y-m-d')
                ];

                $userModel->update($user['id'], ['wallet' => $totalWalletAmount]);

                $walletModel->insert($walletData);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully. Amount return to Your wallet']);
            } else {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully']);
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to cancel order. Order cannot be cancelled at this stage.']);
        }
    }

    public function returningItemRequest()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $data = $this->request->getJSON(true);


        $orderModel = new OrderModel();
        $order_id = $data['order_id'];
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not authorized.']);
        }
        
        $order = $orderModel->where('id', $order_id)->where('user_id', $user['id'] )->first(); // preferred over ->where('id', ...)->first()

        if (!isset($order['id'])){
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found.']);
        }

        $orderProductModel = new OrderProductModel();
        $orderProduct = $orderProductModel->select('product_id')->find($data['order_product_id']);


        $productModel = new ProductModel();
        $product = $productModel->select('is_returnable, return_days, id')->find($orderProduct['product_id']);

        $orderProduct['is_returnable'] = 0;
        $orderProduct['differenceInDays'] = 0;

        // Convert dates to timestamps
        $orderDeliveryDate = strtotime($order['delivery_date']);
        $currentDate = strtotime(date('Y-m-d'));

        // Calculate difference in days (allowing negative values)
        $differenceInSeconds = $currentDate - $orderDeliveryDate;
        $differenceInDays = floor($differenceInSeconds / (60 * 60 * 24)); // Convert seconds to days

        // Check returnable conditions
        if ($product['is_returnable'] && $differenceInDays <= $product['return_days']) {

            $orderReturnRequestModel = new OrderReturnRequestModel();

            $existingRequest = $orderReturnRequestModel
                ->where('order_id', $data['order_id'])
                ->where('order_products_id', $data['order_product_id'])
                ->first();

            if ($existingRequest) {
                // If the request already exists, return an error response
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Already Returning Item Request sent.',
                ]);
            }

            $orderReturnRequestData = [
                'order_id' => $data['order_id'],
                'order_products_id' => $data['order_product_id'],
                'reason' => $data['note'],
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $orderReturnRequestModel->insert($orderReturnRequestData);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Sending Returning Item Request successfully']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to sending Returning Item Request.']);
        }
    }

    public function downloadInvoice()
    {
        $data = $this->request->getJSON(true);

        
        $orderModel = new OrderModel();
        $order_id = $data['order_id'];
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not authorized.']);
        }
        
        $order = $orderModel->where('id', $order_id)->where('user_id', $user['id'] )->first(); // preferred over ->where('id', ...)->first()

        if (!isset($order['id'])){
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found.']);
        }


        $data['settings'] = $this->settings;
        $data['country'] = $this->country;


        $data['address'] = $this->settings['address'];
        $data['call'] = $this->settings['phone'];
        $data['mail'] = $this->settings['email'];
        $data['website'] = $this->settings['website'];

        $data['orderDetails'] = $orderModel->select(
            'orders.id as order_id, orders.order_id as user_order_id,  orders.user_id, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount, 
                    orders.delivery_charge, orders.coupon_amount,  orders.order_date, orders.delivery_date, orders.additional_charge, 
                    orders.timeslot, orders.delivery_boy_id, orders.transaction_id, orders.status, user.name as user_name, 
                    user.mobile as user_mobile, user.email as user_email, address.address, address.area, address.city, address.state, address.pincode, 
                    delivery_boy.name as delivery_boy_name, delivery_boy.mobile as delivery_boy_mobile, 
                    order_status_lists.status as order_status, order_status_lists.color as order_status_color, payment_method.img as payment_method_img, payment_method.title as payment_method_title'
        )
            ->join('delivery_boy', 'delivery_boy.id = orders.delivery_boy_id', 'left')
            ->join('order_status_lists', 'order_status_lists.id = orders.status', 'left')
            ->join('user', 'user.id = orders.user_id', 'left')
            ->join('address', 'address.id = orders.address_id', 'left')
            ->join('payment_method', 'payment_method.id = orders.payment_method_id', 'left')
            ->where('orders.id', $order_id)
            ->first();


        $orderProductModel = new OrderProductModel();

        $data['orderProducts'] = $orderProductModel->select(
            'order_products.product_name, 
            order_products.product_variant_name, 
            order_products.quantity, 
            order_products.price,  
            order_products.tax_percentage, 
            order_products.tax_amount, 
            order_products.product_id, 
            order_products.discounted_price, 
            seller.store_name'
        )
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->join(
                'order_return_request',
                'order_return_request.order_id = order_products.order_id 
                AND order_return_request.status = 4',
                'left'
            )
            ->where('order_products.order_id', $order_id)
            ->where('order_return_request.id IS NULL') // Ensure no matching order_return_request
            ->findAll();

        $data['returnedProducts'] = $orderProductModel->select(
            'order_products.product_name, 
                order_products.product_variant_name, 
                order_products.quantity, 
                order_products.price,  
                order_products.tax_percentage, 
                order_products.tax_amount, 
                order_products.product_id, 
                order_products.discounted_price, 
                seller.store_name'
        )
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->join(
                'order_return_request',
                'order_return_request.order_id = order_products.order_id',
                'left'
            )
            ->where('order_products.order_id', $order_id)
            ->where('order_return_request.status', 4)
            ->findAll();



        $cssPath = FCPATH . 'assets/dist/css/adminlte.min.css'; // Path to your external CSS file
        $css = file_get_contents($cssPath);

        // Load the view into a variable and include inline styles
        $html = view('website/order/invoice',  $data);
        $html = "
                    <html>
                        <head>
                        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
                            <style>{$css} </style>
                            <style>
                                body {
                                    font-family: 'DejaVu Sans', sans-serif;
                                    font-size: 12px !important;
                                }
                                 h1 {
                                    font-size: 16px;
                                }
                                p {
                                    margin: 5px 0;
                                }
                                    .table td,{
                                    padding-block:10px }
                            </style>
                        </head>
                        <body class='text-sm'>
                            {$html}
                        </body>
                    </html>
                ";

        // Initialize Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="invoice_' . $order_id . '.pdf"')
            ->setBody($pdfOutput);
    }

    public function verifyOrderDetails()
    {
        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        // Calculate subtotal and tax totals

        if (isset($data['seller_id']) && $data['seller_id'] !== null) {
            // $data['seller_id'] is set and not null
            list($subTotal, $taxTotal, $discountedPricesaving) = $cartSummery->calculateCartTotals($user['id'], $data['seller_id']);
        } else {
            // $data['seller_id'] is either not set or is null
            list($subTotal, $taxTotal, $discountedPricesaving) = $cartSummery->calculateCartTotals($user['id'], 0);
        }


        // Get deliverable areas and calculate delivery charge
        $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];


        // Calculate coupon amount
        $coupon_amount = 0;
        if (isset($data['appliedCoupon']) && $data['appliedCoupon'] !== null && (int)$data['appliedCoupon']['coupon_id'] > 0) {
            list($coupon_amount,) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);
        }

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $walletBalance = (float) $user['wallet'];
        $total = $subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount;

        list($walletApplied, $remainingWalletBalance) = $cartSummery->calculateWalletAmount($data, $walletBalance, $total);

        $grandTotal = $total - $walletApplied;

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Order Details authorised',
            'deliveryCharge' => $deliveryCharge,
            'subTotal' => $subTotal,
            'taxTotal' => $taxTotal,
            'coupon_amount' => $coupon_amount,
            'wallet_applied' => $walletApplied,
            'remaining_wallet_balance' => $remainingWalletBalance,
            'additional_charge' => $additional_charge,
            'grand_total' => $grandTotal,
            'discountedPricesaving' => $discountedPricesaving
        ]);
    }

    public function placeCODOrder()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        $addressModel = new AddressModel();

        // Validate the address
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        if (isset($data['seller_id']) && $data['seller_id'] !== null) {
            // $data['seller_id'] is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $data['seller_id']);
        } else {
            // $data['seller_id'] is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($data['appliedCoupon']) && $data['appliedCoupon'] !== null && (int)$data['appliedCoupon']['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);
        }


        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $walletBalance = (float) $user['wallet'];
        $total = $subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount;

        list($walletApplied, $remainingWalletBalance) = $cartSummery->calculateWalletAmount($data, $walletBalance, $total);

        $orderModel = new OrderModel();

        $year = date('Y'); // Get the current year
        $randomNumber = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); // Generate a 6-digit random number
        // $order_id = '#' . $year . $randomNumber;

        $cartsModel = new CartsModel();
        $cartItem = $cartsModel->where('user_id', $user['id'])->first();

        $sellerIdForOrderId = str_pad($cartItem['seller_id'], 3, '0', STR_PAD_LEFT);
        $datefororderid = date('ymd');
        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';


        $transaction_id = "cod_" . $randomNumber;

        if ($data['deliveryMethod'] == 'scheduledDelivery') {
            $delivery_date = $data['activeDate'];
            $timeslot = $data['activeTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }

        if (isset($data['paymentMethode'])) {
            $paymentMethode = 1;
        } else {
            $paymentMethode = 0;
        }

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number


        $orderData = [
            'user_id' => $user['id'],
            'address_id' => $address['id'],
            'payment_method_id' => $paymentMethode,
            'coupon_id' => $coupon_id,
            'delivery_date' => $delivery_date,
            'timeslot' => $timeslot,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 2, // received
            'delivery_boy_id' => 0,
            'transaction_id' => $transaction_id,
            'order_delivery_otp' => $order_delivery_otp,
            'subtotal' => $subTotal,
            'tax' => $taxTotal,
            'used_wallet_amount' => $walletApplied,
            'delivery_charge' => $deliveryCharge,
            'coupon_amount' => $coupon_amount,
            'created_at' => date('Y-m-d H:i:s'),
            'additional_charge' => $additional_charge,
            'delivery_method' => $data['deliveryMethod']
        ];

        if ($orderModel->insert($orderData)) {

            $orderId = $orderModel->insertID();

            $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
            $orderModel->update($orderId, ['order_id' => $order_id]);


            $productModel = new ProductModel();
            $variantModel = new ProductVariantsModel();
            $taxModel = new TaxModel();
            $orderProductModel = new OrderProductModel();

            // Fetch all cart items for the current user
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->findAll();
            } else {
                $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
            }

            $subTotal = 0;
            $taxTotal = 0;

            foreach ($cartItems as $cartItem) {
                // Fetch product and variant details
                $product = $productModel
                    ->select('id, product_name, tax_id, seller_id')
                    ->where('id', $cartItem['product_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variant = $variantModel
                    ->select('id, title as product_variant_name, price, discounted_price')
                    ->where('id', $cartItem['product_variant_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variantModel->where('is_unlimited_stock', 0)
                    ->where('id', $cartItem['product_variant_id'])
                    ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                    ->update();

                if ($product && $variant) {
                    $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                    $quantity = (int) $cartItem['quantity'];
                    $lineTotal = $price * $quantity;
                    $subTotal += $lineTotal;

                    // Calculate tax if applicable
                    $taxAmount = 0;
                    $taxPercentage = 0;
                    if ($product['tax_id']) {
                        $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                        if ($tax) {
                            $taxPercentage = (float) $tax['percentage'];
                            $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                            $taxTotal += $taxAmount;
                        }
                    }

                    // Prepare data for insertion into order_products table
                    $orderProductData = [
                        'user_id' => $user['id'],
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'product_variant_id' => $variant['id'],
                        'product_name' => $product['product_name'],
                        'product_variant_name' => $variant['product_variant_name'],
                        'quantity' => $quantity,
                        'price' => $variant['price'],
                        'discounted_price' => $variant['discounted_price'],
                        'tax_amount' => $taxAmount,
                        'tax_percentage' => $taxPercentage,
                        'discount' => $variant['price'] - $variant['discounted_price'],
                        'seller_id' => $product['seller_id'],
                    ];

                    // Insert into order_products table
                    $orderProductModel->insert($orderProductData);
                }
            }

            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }


            if (!is_null($data['appliedCoupon'])) {
                $usedCouponModel = new UsedCouponModel();

                $coupon_id = $data['appliedCoupon']['coupon_id'];

                $usedCouponData = [
                    'coupon_id' => $coupon_id,
                    'user_id' => $user['id'],
                    'date' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId
                ];
                $usedCouponModel->insert($usedCouponData);
            }

            if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
                $walletModel = new WalletModel();

                // Fetch the last closing_amount for the user
                $lastWalletEntry = $walletModel
                    ->select('closing_amount')
                    ->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                    ->first();

                // Calculate the new closing amount after debit
                $walletApplied = (float) $data['wallet']['wallet_applied'];
                $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $walletApplied : $remainingWalletBalance;

                if ($walletApplied > 0) {
                    // Prepare wallet data for insertion
                    $walletData = [
                        'user_id' => $user['id'],
                        'ref_user_id' => 0, // Reference user ID if applicable
                        'amount' => $walletApplied,
                        'closing_amount' => $closingAmount,
                        'flag' => 'debit',
                        'remark' => 'Used in Order Id: ' . $orderId,
                        'date' => date('Y-m-d H:i:s'),
                    ];

                    // Insert into wallet table
                    $walletModel->insert($walletData);
                }
                $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id' => $orderId,
                'status' => 2,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($orderStatusesData);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully', 'order_id' => $orderId, 'base_url' => base_url()]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function createRazorpayOrder()
    {
        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        $addressModel = new AddressModel();

        // Validate the address
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        // Calculate subtotal and tax totals
        if (isset($data['seller_id']) && $data['seller_id'] !== null) {
            // $data['seller_id'] is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $data['seller_id']);
        } else {
            // $data['seller_id'] is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        // Get deliverable areas and calculate delivery charge
        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];


        // Calculate coupon amount
        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($data['appliedCoupon']) && $data['appliedCoupon'] !== null && (int)$data['appliedCoupon']['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);
        }


        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $walletBalance = (float) $user['wallet'];
        $total = $subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount;

        list($walletApplied,) = $cartSummery->calculateWalletAmount($data, $walletBalance, $total);

        $cartsModel = new CartsModel();
        $cartItem = $cartsModel->where('user_id', $user['id'])->first();

        $sellerIdForOrderId = str_pad($cartItem['seller_id'], 3, '0', STR_PAD_LEFT);
        $datefororderid = date('ymd');
        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        $paymentAmount = (round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount - $walletApplied, 2)) * 100;

        $api = new Api($this->razorpayApiKey, $this->razorpayApiSecret);

        // Order data
        $orderData = [
            'receipt'         => 'RZP_' . time(),
            'amount'          => (int)$paymentAmount,
            'currency'        => $this->country['currency_shortcut'],
        ];

        // Create order
        $order = $api->order->create($orderData);

        $reflection = new ReflectionClass($order);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $orderAttributes = $property->getValue($order);

        if ($data['deliveryMethod'] == 'scheduledDelivery') {
            $delivery_date = $data['activeDate'];
            $timeslot = $data['activeTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }
        $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

        $orderData = [
            'user_id' => $user['id'],
            'address_id' => $address['id'],
            'payment_method_id' => $data['paymentMethode'],
            'coupon_id' => $coupon_id,
            'delivery_date' => $delivery_date,
            'timeslot' => $timeslot,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 1, //payment pending
            'delivery_boy_id' => 0,
            'order_delivery_otp' => $order_delivery_otp,
            'subtotal' => $subTotal,
            'tax' => $taxTotal,
            'used_wallet_amount' => $walletApplied,
            'delivery_charge' => $deliveryCharge,
            'coupon_amount' => $coupon_amount,
            'created_at' => date('Y-m-d H:i:s'),
            'additional_charge' => $additional_charge,
            'delivery_method' => $data['deliveryMethod']
        ];

        $orderModel = new OrderModel();

        if ($orderModel->insert($orderData)) {

            $orderId = $orderModel->insertID();

            $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
            $orderModel->update($orderId, ['order_id' => $order_id]);

            $cartsModel = new CartsModel();
            $productModel = new ProductModel();
            $variantModel = new ProductVariantsModel();
            $taxModel = new TaxModel();
            $orderProductModel = new OrderProductModel();

            // Fetch all cart items for the current user
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->findAll();
            } else {
                $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
            }

            $subTotal = 0;
            $taxTotal = 0;

            foreach ($cartItems as $cartItem) {
                // Fetch product and variant details
                $product = $productModel
                    ->select('id, product_name, tax_id, seller_id')
                    ->where('id', $cartItem['product_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variant = $variantModel
                    ->select('id, title as product_variant_name, price, discounted_price')
                    ->where('id', $cartItem['product_variant_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variantModel->where('is_unlimited_stock', 0)
                    ->where('id', $cartItem['product_variant_id'])
                    ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                    ->update();

                if ($product && $variant) {
                    $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                    $quantity = (int) $cartItem['quantity'];
                    $lineTotal = $price * $quantity;
                    $subTotal += $lineTotal;

                    // Calculate tax if applicable
                    $taxAmount = 0;
                    $taxPercentage = 0;
                    if ($product['tax_id']) {
                        $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                        if ($tax) {
                            $taxPercentage = (float) $tax['percentage'];
                            $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                            $taxTotal += $taxAmount;
                        }
                    }

                    // Prepare data for insertion into order_products table
                    $orderProductData = [
                        'user_id' => $user['id'],
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'product_variant_id' => $variant['id'],
                        'product_name' => $product['product_name'],
                        'product_variant_name' => $variant['product_variant_name'],
                        'quantity' => $quantity,
                        'price' => $variant['price'],
                        'discounted_price' => $variant['discounted_price'],
                        'tax_amount' => $taxAmount,
                        'tax_percentage' => $taxPercentage,
                        'discount' => $variant['price'] - $variant['discounted_price'],
                        'seller_id' => $product['seller_id'],
                    ];

                    // Insert into order_products table
                    $orderProductModel->insert($orderProductData);
                }
            }

            if (!is_null($data['appliedCoupon'])) {
                $usedCouponModel = new UsedCouponModel();

                $coupon_id = $data['appliedCoupon']['coupon_id'];

                $usedCouponData = [
                    'coupon_id' => $coupon_id,
                    'user_id' => $user['id'],
                    'date' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId
                ];
                $usedCouponModel->insert($usedCouponData);
            }

            if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
                $walletModel = new WalletModel();

                // Fetch the last closing_amount for the user
                $lastWalletEntry = $walletModel
                    ->select('closing_amount')
                    ->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                    ->first();

                // Calculate the new closing amount after debit
                $walletApplied = (float) $data['wallet']['wallet_applied'];
                $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $walletApplied : 0;

                // Prepare wallet data for insertion
                if ($walletApplied > 0) {
                    $walletData = [
                        'user_id' => $user['id'],
                        'ref_user_id' => 0, // Reference user ID if applicable
                        'amount' => $walletApplied,
                        'closing_amount' => $closingAmount,
                        'flag' => 'debit',
                        'remark' => 'Used in Order Id: ' . $orderId,
                        'date' => date('Y-m-d H:i:s'),
                    ];

                    // Insert into wallet table
                    $walletModel->insert($walletData);
                }


                $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id' => $orderId,
                'status' => 1,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($orderStatusesData);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order created, Payment Pending', 'order_id' => $orderId, 'razorpay_order_id' => $orderAttributes['id'], 'amount' => round($paymentAmount, 2)]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function verifyRazorpayPayment()
    {
        $data = $this->request->getJSON(true);

        $razorpayPaymentId = $data['razorpay_payment_id'];
        $razorpayOrderId   = $data['razorpay_order_id'];
        $razorpaySignature = $data['razorpay_signature'];

        try {
            $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $this->razorpayApiSecret);

            if (hash_equals($generatedSignature, $razorpaySignature)) {
                // Update payment status in the database


                $orderModel = new OrderModel();
                $orderModel->where('id', $data['order_id'])
                    ->set('transaction_id', $razorpayPaymentId)
                    ->set('payment_json', json_encode($data))
                    ->set('status', 2)
                    ->update();

                $cartsModel = new CartsModel();
                $userModel = new UserModel();
                if (session()->get('login_type') == 'email') {
                    $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
                }

                if (session()->get('login_type') == 'mobile') {
                    $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
                }
                // Clear the cart after placing the order
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->delete();
                } else {
                    $cartsModel->where('user_id', $user['id'])->delete();
                }

                $orderStatusesModel = new OrderStatusesModel();
                $data = [
                    'orders_id' => $data['order_id'],
                    'status' => 2,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($data);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url()]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Payment Signature not Verified']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function createPaypalOrder()
    {
        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);
        $user = null;
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        $addressModel = new AddressModel();
        $address = $addressModel->where('user_id', $user['id'])->where('is_delete', 0)->where('status', 1)->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        if (isset($data['seller_id']) && $data['seller_id'] !== null) {
            // $data['seller_id'] is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $data['seller_id']);
        } else {
            // $data['seller_id'] is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }
        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($data['appliedCoupon']) && $data['appliedCoupon'] !== null && (int)$data['appliedCoupon']['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);
        }

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = $additional_charge_status == 1 ? (float)$this->settings['additional_charge'] : 0;

        $walletBalance = (float) $user['wallet'];
        $total = round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount, 2);

        list($walletApplied,) = $cartSummery->calculateWalletAmount($data, $walletBalance, $total);

        $cartsModel = new CartsModel();
        $cartItem = $cartsModel->where('user_id', $user['id'])->first();

        $sellerIdForOrderId = str_pad($cartItem['seller_id'], 3, '0', STR_PAD_LEFT);
        $datefororderid = date('ymd');
        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);
        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        $paymentAmount = round($total - $walletApplied, 2);

        $url = 'https://api-m.paypal.com/v2/checkout/orders';
        $headers = [
            'Authorization: Basic ' . base64_encode($this->paypalApiKey . ':' . $this->paypalApiSecret),
            'Content-Type: application/json',
        ];

        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();

        if ($this->settings['seller_only_one_seller_cart']) {
            $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->findAll();
        } else {
            $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
        }
        $items = [];

        foreach ($cartItems as $cartItem) {
            $product = $productModel->select('product_name')->where('id', $cartItem['product_id'])->where('is_delete', 0)->first();
            $variant = $variantModel->select('price, discounted_price')->where('id', $cartItem['product_variant_id'])->where('is_delete', 0)->first();

            $basePrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $variant['price'];
            $finalPrice = number_format($basePrice, 2, '.', '');

            $items[] = [
                'name' => $product['product_name'],
                'unit_amount' => [
                    'currency_code' => $this->country['currency_shortcut'],
                    'value' => $finalPrice,
                ],
                'quantity' => $cartItem['quantity'],
            ];
        }

        $purchaseData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $this->country['currency_shortcut'],
                        'value' => $paymentAmount,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($subTotal, 2),
                            ],
                            'shipping' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($deliveryCharge + $additional_charge, 2),
                            ],
                            'discount' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($coupon_amount + $walletApplied, 2),
                            ],
                            'tax_total' => [
                                'currency_code' => $this->country['currency_shortcut'],
                                'value' => number_format($taxTotal, 2),
                            ]
                        ],
                    ],
                    'items' => $items,
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($purchaseData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            $order = json_decode($response, true);

            if ($data['deliveryMethod'] == 'scheduledDelivery') {
                $delivery_date = $data['activeDate'];
                $timeslot = $data['activeTime'];
            } else {
                $delivery_date = null;
                $timeslot = null;
            }
            $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

            $orderData = [
                'user_id' => $user['id'],
                'address_id' => $address['id'],
                'payment_method_id' => $data['paymentMethode'],
                'coupon_id' => $coupon_id,
                'delivery_date' => $delivery_date,
                'timeslot' => $timeslot,
                'order_date' => date('Y-m-d H:i:s'),
                'status' => 1, //payment pending
                'delivery_boy_id' => 0,
                'subtotal' => $subTotal,
                'order_delivery_otp' => $order_delivery_otp,
                'tax' => $taxTotal,
                'used_wallet_amount' => $walletApplied,
                'delivery_charge' => $deliveryCharge,
                'coupon_amount' => $coupon_amount,
                'created_at' => date('Y-m-d H:i:s'),
                'additional_charge' => $additional_charge,
                'delivery_method' => $data['deliveryMethod']
            ];

            $orderModel = new OrderModel();

            if ($orderModel->insert($orderData)) {

                $orderId = $orderModel->insertID();

                $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
                $orderModel->update($orderId, ['order_id' => $order_id]);

                $cartsModel = new CartsModel();
                $productModel = new ProductModel();
                $variantModel = new ProductVariantsModel();
                $taxModel = new TaxModel();
                $orderProductModel = new OrderProductModel();

                // Fetch all cart items for the current user
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->findAll();
                } else {
                    $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
                }

                $subTotal = 0;
                $taxTotal = 0;

                foreach ($cartItems as $cartItem) {
                    // Fetch product and variant details
                    $product = $productModel
                        ->select('id, product_name, tax_id, seller_id')
                        ->where('id', $cartItem['product_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variant = $variantModel
                        ->select('id, title as product_variant_name, price, discounted_price')
                        ->where('id', $cartItem['product_variant_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variantModel->where('is_unlimited_stock', 0)
                        ->where('id', $cartItem['product_variant_id'])
                        ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                        ->update();

                    if ($product && $variant) {
                        $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                        $quantity = (int) $cartItem['quantity'];
                        $lineTotal = $price * $quantity;
                        $subTotal += $lineTotal;

                        // Calculate tax if applicable
                        $taxAmount = 0;
                        $taxPercentage = 0;
                        if ($product['tax_id']) {
                            $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                            if ($tax) {
                                $taxPercentage = (float) $tax['percentage'];
                                $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                                $taxTotal += $taxAmount;
                            }
                        }

                        // Prepare data for insertion into order_products table
                        $orderProductData = [
                            'user_id' => $user['id'],
                            'order_id' => $orderId,
                            'product_id' => $product['id'],
                            'product_variant_id' => $variant['id'],
                            'product_name' => $product['product_name'],
                            'product_variant_name' => $variant['product_variant_name'],
                            'quantity' => $quantity,
                            'price' => $variant['price'],
                            'discounted_price' => $variant['discounted_price'],
                            'tax_amount' => $taxAmount,
                            'tax_percentage' => $taxPercentage,
                            'discount' => $variant['price'] - $variant['discounted_price'],
                            'seller_id' => $product['seller_id'],
                        ];

                        // Insert into order_products table
                        $orderProductModel->insert($orderProductData);
                    }
                }


                if (!is_null($data['appliedCoupon'])) {
                    $usedCouponModel = new UsedCouponModel();

                    $coupon_id = $data['appliedCoupon']['coupon_id'];

                    $usedCouponData = [
                        'coupon_id' => $coupon_id,
                        'user_id' => $user['id'],
                        'date' => date('Y-m-d H:i:s'),
                        'order_id' => $orderId
                    ];
                    $usedCouponModel->insert($usedCouponData);
                }

                if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
                    $walletModel = new WalletModel();

                    // Fetch the last closing_amount for the user
                    $lastWalletEntry = $walletModel
                        ->select('closing_amount')
                        ->where('user_id', $user['id'])
                        ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                        ->first();

                    // Calculate the new closing amount after debit
                    $walletApplied = (float) $data['wallet']['wallet_applied'];
                    $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $walletApplied : 0;

                    // Prepare wallet data for insertion
                    if ($walletApplied > 0) {
                        $walletData = [
                            'user_id' => $user['id'],
                            'ref_user_id' => 0, // Reference user ID if applicable
                            'amount' => $walletApplied,
                            'closing_amount' => $closingAmount,
                            'flag' => 'debit',
                            'remark' => 'Used in Order Id: ' . $orderId,
                            'date' => date('Y-m-d H:i:s'),
                        ];

                        // Insert into wallet table
                        $walletModel->insert($walletData);
                    }


                    $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
                }

                $orderStatusesModel = new OrderStatusesModel();
                $orderStatusesData = [
                    'orders_id' => $orderId,
                    'status' => 1,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($orderStatusesData);


                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Order created, Payment Pending',
                    'order_id' => $order['id'],
                    'amount' => $paymentAmount,
                    'purchaseData' => $purchaseData
                ]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
            }
        } else {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Unable to create PayPal order.',
                'details' => json_decode($response, true),
                'curl_error' => $curlError,
            ]);
        }
    }

    public function capturePaypalOrder()
    {
        $requestData = $this->request->getJSON(true);
        $orderId = $requestData['orderID'];
        $user = null;
        $url = "https://api-m.paypal.com/v2/checkout/orders/$orderId/capture";

        $headers = [
            'Authorization: Basic ' . base64_encode($this->paypalApiKey . ':' . $this->paypalApiSecret),
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            $captureData = json_decode($response, true);

            $orderModel = new OrderModel();
            $orderModel->where('order_id', $orderId)
                ->set('transaction_id', $captureData['purchase_units'][0]['payments']['captures'][0]['id']) // Correct way to access associative array
                ->set('status', 2)
                ->set('payment_json', json_encode($captureData))
                ->update();

            $order = $orderModel->where('order_id', $orderId)->first();

            $cartsModel = new CartsModel();
            $userModel = new UserModel();
            if (session()->get('login_type') == 'email') {
                $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (session()->get('login_type') == 'mobile') {
                $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
            }
            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $requestData['seller_id'])->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $data = [
                'orders_id' => $order['id'],
                'status' => 2,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($data);


            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url(), 'order_id' => $order['id']]);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Unable to capture PayPal payment.']);
        }
    }

    public function createPaystackOrder()
    {
        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);
        $user = null;

        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        $addressModel = new AddressModel();

        // Validate the address
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        // Calculate subtotal and tax totals
        if (isset($data['seller_id']) && $data['seller_id'] !== null) {
            // $data['seller_id'] is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $data['seller_id']);
        } else {
            // $data['seller_id'] is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        // Get deliverable areas and calculate delivery charge
        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];


        // Calculate coupon amount
        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($data['appliedCoupon']) && $data['appliedCoupon'] !== null && (int)$data['appliedCoupon']['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);
        }


        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $walletBalance = (float) $user['wallet'];
        $total = round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount, 2);

        list($walletApplied,) = $cartSummery->calculateWalletAmount($data, $walletBalance, $total);

        $cartsModel = new CartsModel();
        $cartItem = $cartsModel->where('user_id', $user['id'])->first();

        $sellerIdForOrderId = str_pad($cartItem['seller_id'], 3, '0', STR_PAD_LEFT);
        $datefororderid = date('ymd');
        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        $paymentAmount = round($total - $walletApplied, 2);

        if ($data['deliveryMethod'] == 'scheduledDelivery') {
            $delivery_date = $data['activeDate'];
            $timeslot = $data['activeTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }
        $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

        $orderData = [
            'user_id' => $user['id'],
            'address_id' => $address['id'],
            'payment_method_id' => $data['paymentMethode'],
            'coupon_id' => $coupon_id,
            'delivery_date' => $delivery_date,
            'timeslot' => $timeslot,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 1, //payment pending
            'delivery_boy_id' => 0,
            'subtotal' => $subTotal,
            'tax' => $taxTotal,
            'order_delivery_otp' => $order_delivery_otp,
            'used_wallet_amount' => $walletApplied,
            'delivery_charge' => $deliveryCharge,
            'coupon_amount' => $coupon_amount,
            'created_at' => date('Y-m-d H:i:s'),
            'additional_charge' => $additional_charge,
            'delivery_method' => $data['deliveryMethod']
        ];

        $orderModel = new OrderModel();

        if ($orderModel->insert($orderData)) {

            $orderId = $orderModel->insertID();

            $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
            $orderModel->update($orderId, ['order_id' => $order_id]);

            $cartsModel = new CartsModel();
            $productModel = new ProductModel();
            $variantModel = new ProductVariantsModel();
            $taxModel = new TaxModel();
            $orderProductModel = new OrderProductModel();

            // Fetch all cart items for the current user
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->findAll();
            } else {
                $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
            }

            $subTotal = 0;
            $taxTotal = 0;

            foreach ($cartItems as $cartItem) {
                // Fetch product and variant details
                $product = $productModel
                    ->select('id, product_name, tax_id, seller_id')
                    ->where('id', $cartItem['product_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variant = $variantModel
                    ->select('id, title as product_variant_name, price, discounted_price')
                    ->where('id', $cartItem['product_variant_id'])
                    ->where('is_delete', 0)
                    ->first();

                $variantModel->where('is_unlimited_stock', 0)
                    ->where('id', $cartItem['product_variant_id'])
                    ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                    ->update();

                if ($product && $variant) {
                    $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                    $quantity = (int) $cartItem['quantity'];
                    $lineTotal = $price * $quantity;
                    $subTotal += $lineTotal;

                    // Calculate tax if applicable
                    $taxAmount = 0;
                    $taxPercentage = 0;
                    if ($product['tax_id']) {
                        $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                        if ($tax) {
                            $taxPercentage = (float) $tax['percentage'];
                            $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                            $taxTotal += $taxAmount;
                        }
                    }

                    // Prepare data for insertion into order_products table
                    $orderProductData = [
                        'user_id' => $user['id'],
                        'order_id' => $orderId,
                        'product_id' => $product['id'],
                        'product_variant_id' => $variant['id'],
                        'product_name' => $product['product_name'],
                        'product_variant_name' => $variant['product_variant_name'],
                        'quantity' => $quantity,
                        'price' => $variant['price'],
                        'discounted_price' => $variant['discounted_price'],
                        'tax_amount' => $taxAmount,
                        'tax_percentage' => $taxPercentage,
                        'discount' => $variant['price'] - $variant['discounted_price'],
                        'seller_id' => $product['seller_id'],
                    ];

                    // Insert into order_products table
                    $orderProductModel->insert($orderProductData);
                }
            }


            if (!is_null($data['appliedCoupon'])) {
                $usedCouponModel = new UsedCouponModel();

                $coupon_id = $data['appliedCoupon']['coupon_id'];

                $usedCouponData = [
                    'coupon_id' => $coupon_id,
                    'user_id' => $user['id'],
                    'date' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId
                ];
                $usedCouponModel->insert($usedCouponData);
            }

            if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
                $walletModel = new WalletModel();

                // Fetch the last closing_amount for the user
                $lastWalletEntry = $walletModel
                    ->select('closing_amount')
                    ->where('user_id', $user['id'])
                    ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                    ->first();

                // Calculate the new closing amount after debit
                $walletApplied = (float) $data['wallet']['wallet_applied'];
                $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $walletApplied : 0;

                if ($walletApplied > 0) {
                    // Prepare wallet data for insertion
                    $walletData = [
                        'user_id' => $user['id'],
                        'ref_user_id' => 0, // Reference user ID if applicable
                        'amount' => $walletApplied,
                        'closing_amount' => $closingAmount,
                        'flag' => 'debit',
                        'remark' => 'Used in Order Id: ' . $orderId,
                        'date' => date('Y-m-d H:i:s'),
                    ];

                    // Insert into wallet table
                    $walletModel->insert($walletData);
                }

                $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
            }


            $orderStatusesModel = new OrderStatusesModel();
            $orderStatusesData = [
                'orders_id' => $orderId,
                'status' => 1,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($orderStatusesData);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Created Successfully', 'order_id' => $orderId, 'amount' => $paymentAmount]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
        }
    }

    public function verifyPaystackOrder()
    {

        $requestData = $this->request->getJSON(true);
        $reference = $requestData['reference'];
        $transaction = $requestData['transaction'];
        $amount = $requestData['amount'];
        $order_id = $requestData['order_id'];

        $orderModel = new OrderModel();
        $order = $orderModel->where('id', $order_id)->first();

        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $total = $order['subtotal'] + $order['tax'] + $additional_charge + $order['delivery_charge'] - $order['coupon_amount'] - $order['used_wallet_amount'];

        if (round($amount, 2) == round($total, 2)) {
            $orderModel->where('id', $order_id)
                ->set('transaction_id', $transaction)
                ->set('order_id', $reference)
                ->set('status', 2)
                ->set('payment_json', json_encode($requestData))
                ->update();

            $cartsModel = new CartsModel();
            $userModel = new UserModel();
            if (session()->get('login_type') == 'email') {
                $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (session()->get('login_type') == 'mobile') {
                $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
            }
            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $requestData['seller_id'])->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $data = [
                'orders_id' => $order_id,
                'status' => 2,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($data);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url()]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.' . $amount . ' ' . $total]);
        }
    }

    public function createCashFreeOrder()
    {
        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);
        $user = null;

        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        $addressModel = new AddressModel();

        // Validate the address
        $address = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        if (!$address) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Enter Delivery Address Details.'
            ]);
        }

        // Calculate subtotal and tax totals
        if (isset($data['seller_id']) && $data['seller_id'] !== null) {
            // $data['seller_id'] is set and not null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], $data['seller_id']);
        } else {
            // $data['seller_id'] is either not set or is null
            list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id'], 0);
        }

        // Get deliverable areas and calculate delivery charge
        $deliveryDetails = $deliveryDetails = $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);
        $deliveryCharge = $deliveryDetails['deliveryCharge'];

        // Calculate coupon amount
        $coupon_amount = 0;
        $coupon_id = 0;
        if (isset($data['appliedCoupon']) && $data['appliedCoupon'] !== null && (int)$data['appliedCoupon']['coupon_id'] > 0) {
            list($coupon_amount, $coupon_id) = $cartSummery->calculateCouponAmount($data['appliedCoupon'], $subTotal, $user['id']);
        }


        $additional_charge_status = $this->settings['additional_charge_status'];
        $additional_charge = 0;

        if ($additional_charge_status == 1) {
            $additional_charge = (float)$this->settings['additional_charge'];
        }

        $walletBalance = (float) $user['wallet'];
        $total = round($subTotal + $taxTotal + $deliveryCharge + $additional_charge - $coupon_amount, 2);

        list($walletApplied,) = $cartSummery->calculateWalletAmount($data, $walletBalance, $total);

        $cartsModel = new CartsModel();
        $cartItem = $cartsModel->where('user_id', $user['id'])->first();

        $sellerIdForOrderId = str_pad($cartItem['seller_id'], 3, '0', STR_PAD_LEFT);
        $datefororderid = date('ymd');
        $base_order_id = 'ORD-' . $datefororderid . '-' . $sellerIdForOrderId . '-';

        $remainingAmount = $this->settings['minimum_order_amount'] - ($subTotal + $taxTotal);

        if ($remainingAmount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You need to add ' . $this->country['currency_symbol'] . $remainingAmount . ' more to place your order. Please add more items to proceed.'
            ]);
        }

        $paymentAmount = round($total - $walletApplied, 2);

        Cashfree::$XClientId = $this->cashFreeApiKey;
        Cashfree::$XClientSecret = $this->cashFreeApiSecret;
        Cashfree::$XEnvironment = Cashfree::$PRODUCTION; // Use Cashfree::$PRODUCTION for production

        $cashfree = new \Cashfree\Cashfree();
        $x_api_version = "2022-09-01";
        $create_orders_request = new \Cashfree\Model\CreateOrderRequest();

        $create_orders_request->setOrderAmount($paymentAmount);
        $create_orders_request->setOrderCurrency($this->country['currency_shortcut']);

        $customer_details = new \Cashfree\Model\CustomerDetails();
        $customer_details->setCustomerId($user['name']);
        $customer_details->setCustomerPhone($user['mobile']);
        $create_orders_request->setCustomerDetails($customer_details);

        if ($data['deliveryMethod'] == 'scheduledDelivery') {
            $delivery_date = $data['activeDate'];
            $timeslot = $data['activeTime'];
        } else {
            $delivery_date = null;
            $timeslot = null;
        }

        try {
            $result = $cashfree->PGCreateOrder($x_api_version, $create_orders_request);
            $order_delivery_otp = str_pad(mt_rand(0000, 9999), 4, '0', STR_PAD_LEFT); // Generate a 4-digit random number

            $orderData = [
                'user_id' => $user['id'],
                'address_id' => $address['id'],
                'payment_method_id' => $data['paymentMethode'],
                'coupon_id' => $coupon_id,
                'delivery_date' => $delivery_date,
                'timeslot' => $timeslot,
                'order_date' => date('Y-m-d H:i:s'),
                'status' => 1, //payment pending
                'delivery_boy_id' => 0,
                'subtotal' => $subTotal,
                'order_delivery_otp' => $order_delivery_otp,
                'tax' => $taxTotal,
                'used_wallet_amount' => $walletApplied,
                'delivery_charge' => $deliveryCharge,
                'coupon_amount' => $coupon_amount,
                'created_at' => date('Y-m-d H:i:s'),
                'additional_charge' => $additional_charge,
                'delivery_method' => $data['deliveryMethod']
            ];

            $orderModel = new OrderModel();

            if ($orderModel->insert($orderData)) {

                $orderId = $orderModel->insertID();

                $order_id = $base_order_id . str_pad($orderId, 4, '0', STR_PAD_LEFT);
                $orderModel->update($orderId, ['order_id' => $order_id]);

                $cartsModel = new CartsModel();
                $productModel = new ProductModel();
                $variantModel = new ProductVariantsModel();
                $taxModel = new TaxModel();
                $orderProductModel = new OrderProductModel();

                // Fetch all cart items for the current user
                if ($this->settings['seller_only_one_seller_cart']) {
                    $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->findAll();
                } else {
                    $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();
                }

                $subTotal = 0;
                $taxTotal = 0;

                foreach ($cartItems as $cartItem) {
                    // Fetch product and variant details
                    $product = $productModel
                        ->select('id, product_name, tax_id, seller_id')
                        ->where('id', $cartItem['product_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variant = $variantModel
                        ->select('id, title as product_variant_name, price, discounted_price')
                        ->where('id', $cartItem['product_variant_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $variantModel->where('is_unlimited_stock', 0)
                        ->where('id', $cartItem['product_variant_id'])
                        ->set('stock', 'stock - ' . (int)$cartItem['quantity'], false)
                        ->update();

                    if ($product && $variant) {
                        $price = (float) ($variant['discounted_price'] ?: $variant['price']);
                        $quantity = (int) $cartItem['quantity'];
                        $lineTotal = $price * $quantity;
                        $subTotal += $lineTotal;

                        // Calculate tax if applicable
                        $taxAmount = 0;
                        $taxPercentage = 0;
                        if ($product['tax_id']) {
                            $tax = $taxModel->select('percentage')->where('id', $product['tax_id'])->first();
                            if ($tax) {
                                $taxPercentage = (float) $tax['percentage'];
                                $taxAmount = ($price * $taxPercentage / 100) * $quantity;
                                $taxTotal += $taxAmount;
                            }
                        }

                        // Prepare data for insertion into order_products table
                        $orderProductData = [
                            'user_id' => $user['id'],
                            'order_id' => $orderId,
                            'product_id' => $product['id'],
                            'product_variant_id' => $variant['id'],
                            'product_name' => $product['product_name'],
                            'product_variant_name' => $variant['product_variant_name'],
                            'quantity' => $quantity,
                            'price' => $variant['price'],
                            'discounted_price' => $variant['discounted_price'],
                            'tax_amount' => $taxAmount,
                            'tax_percentage' => $taxPercentage,
                            'discount' => $variant['price'] - $variant['discounted_price'],
                            'seller_id' => $product['seller_id'],
                        ];

                        // Insert into order_products table
                        $orderProductModel->insert($orderProductData);
                    }
                }

                if (!is_null($data['appliedCoupon'])) {
                    $usedCouponModel = new UsedCouponModel();

                    $coupon_id = $data['appliedCoupon']['coupon_id'];

                    $usedCouponData = [
                        'coupon_id' => $coupon_id,
                        'user_id' => $user['id'],
                        'date' => date('Y-m-d H:i:s'),
                        'order_id' => $orderId
                    ];
                    $usedCouponModel->insert($usedCouponData);
                }

                if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
                    $walletModel = new WalletModel();

                    // Fetch the last closing_amount for the user
                    $lastWalletEntry = $walletModel
                        ->select('closing_amount')
                        ->where('user_id', $user['id'])
                        ->orderBy('id', 'DESC') // Assuming `id` is auto-incremented
                        ->first();

                    // Calculate the new closing amount after debit
                    $walletApplied = (float) $data['wallet']['wallet_applied'];
                    $closingAmount = $lastWalletEntry ? (float) $lastWalletEntry['closing_amount'] - $walletApplied : 0;

                    if ($walletApplied > 0) {
                        // Prepare wallet data for insertion
                        $walletData = [
                            'user_id' => $user['id'],
                            'ref_user_id' => 0, // Reference user ID if applicable
                            'amount' => $walletApplied,
                            'closing_amount' => $closingAmount,
                            'flag' => 'debit',
                            'remark' => 'Used in Order Id: ' . $orderId,
                            'date' => date('Y-m-d H:i:s'),
                        ];

                        // Insert into wallet table
                        $walletModel->insert($walletData);
                    }

                    $userModel->set('wallet', $closingAmount)->where('id', $user['id'])->update();
                }


                $orderStatusesModel = new OrderStatusesModel();
                $orderStatusesData = [
                    'orders_id' => $orderId,
                    'status' => 1,
                    'created_by' => $user['id'],
                    'user_type' => 'Customer',
                    'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
                ];
                $orderStatusesModel->insert($orderStatusesData);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Order created, Payment Pending', 'order_id' => $orderId, 'cashfree_order_id' => $result[0]['order_id'], 'amount' => $paymentAmount, 'payment_session_id' => $result[0]['payment_session_id']]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.']);
            }
        } catch (Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function confirmCashFreeOrder()
    {
        $data = $this->request->getJSON(true);

        Cashfree::$XClientId = $this->cashFreeApiKey;
        Cashfree::$XClientSecret = $this->cashFreeApiSecret;
        Cashfree::$XEnvironment = Cashfree::$PRODUCTION;

        $x_api_version = "2023-08-01";
        $cashfree = new \Cashfree\Cashfree();

        try {
            $response = $cashfree->PGFetchOrder($x_api_version, $data['cashfree_order_id']);
            $orderModel = new OrderModel();
            $orderModel->where('id', $data['order_id'])
                ->set('transaction_id', $data['payment_session_id'])
                ->set('status', 2)
                ->set('payment_json', json_encode($response))
                ->update();

            $cartsModel = new CartsModel();
            $userModel = new UserModel();
            if (session()->get('login_type') == 'email') {
                $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (session()->get('login_type') == 'mobile') {
                $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
            }
            // Clear the cart after placing the order
            if ($this->settings['seller_only_one_seller_cart']) {
                $cartsModel->where('user_id', $user['id'])->where('seller_id', $data['seller_id'])->delete();
            } else {
                $cartsModel->where('user_id', $user['id'])->delete();
            }

            $orderStatusesModel = new OrderStatusesModel();
            $data = [
                'orders_id' => $data['order_id'],
                'status' => 2,
                'created_by' => $user['id'],
                'user_type' => 'Customer',
                'created_at' => date('Y-m-d H:i:s'), // Use the current timestamp
            ];
            $orderStatusesModel->insert($data);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Order Placed Successfully, Payment verified successfully.', 'base_url' => base_url()],);
        } catch (Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to Placed Order. Please try again later.' . $e]);
        }
    }
}
