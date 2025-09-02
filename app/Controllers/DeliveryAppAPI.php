<?php

namespace App\Controllers;

use App\Models\AddressModel;
use App\Models\CityModel;
use App\Models\DeliveryBoyModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\CountryModel;
use App\Models\DeliveryBoyFundTransferModel;
use App\Models\DeliveryBoyTransactionModel;
use App\Models\DeviceTokenModel;
use App\Models\OrderProductModel;
use App\Models\OrderReturnRequestModel;
use App\Models\OrderStatusesModel;
use App\Models\OrderStatusListsModel;
use App\Models\SellerModel;
use App\Models\SellerWalletTransactionModel;
use App\Models\DeliveryTrackingModel;
use App\Models\LanguageModel;

class DeliveryAppAPI extends BaseController
{
    use ResponseTrait;

    private $secretKey;

    public function __construct()
    {
        $this->secretKey = getenv('JWT_SECRET');
    }

    public function getActiveCountry()
    {
        $countryModel = new CountryModel();
        $row = $countryModel->where('is_active', 1)->first();
        return $this->response->setJSON($row);
    }

    public function login()
    {
        $request = service('request');
        $decodeData = $request->getJSON(true);

        if (empty($decodeData['mobile']) || empty($decodeData['password'])) {
            return $this->respond([
                "status" => 401,
                "result" => "false",
                "message"    => "Mobile and Password are required."
            ]);
        }

        $mobile = $decodeData['mobile'];
        $password = $decodeData['password'];

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel->where('mobile', $mobile)->first();

        if($deliveryBoy['is_delete'] == 1){
            return $this->respond([
                "status" => 401,
                "result" => "false",
                "message"    => "Account has been deleted. Contact Admin"
            ]);
        }

        if ($deliveryBoy && password_verify($password, $deliveryBoy['password'])) {
            // Generate a custom token
            $token = $this->generateToken($deliveryBoy['mobile']);

            $deviceTokenModel = new DeviceTokenModel();

            $deviceTokenModel->insert(['user_type' => 3, 'user_id' => $deliveryBoy['id'], 'app_key' => $decodeData['fcmToken']]);

            return $this->respond([
                "status" => 200,
                "result" => "true",
                "message"    => "Login successful!",
                "name"   => $deliveryBoy['name'],
                "token"  => $token
            ]);
        } else {
            return $this->respond([
                "status" => 401,
                "result" => "false",
                "message"    => "Invalid mobile number or password."
            ]);
        }
    }

    private function generateToken($deliveryMobile)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'mobile' => $deliveryMobile,
            'iat' => time() // Issued at time
        ]);

        // Base64 encode header and payload
        $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        // Create signature
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secretKey, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    private function validateToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false; // Invalid token format
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Verify signature
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secretKey, true);
        $expectedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        if (!hash_equals($expectedSignature, $base64UrlSignature)) {
            return false; // Invalid signature
        }

        // Decode payload
        $payload = json_decode(base64_decode($base64UrlPayload), true);

        return $payload; // Return decoded payload if valid
    }

    private function authorizedToken()
    {
        // Check if the Authorization header exists
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->failUnauthorized('Authorization token is required');
        }

        // Extract the token from the Authorization header
        $token = str_replace('Bearer ', '', $authHeader);

        // Validate the token and get payload
        $payload = $this->validateToken($token);

        if (!$payload || !isset($payload['mobile'])) {
            return $this->failUnauthorized('Invalid or missing token payload');
        }

        return $payload;
    }

    public function updateActiveStatus()
    {
        // Get JSON data from the request
        $data = $this->request->getJSON(true);

        $payload = $this->authorizedToken();

        // Initialize the DeliveryBoy model
        $deliveryBoyModel = new DeliveryBoyModel();

        // Find the delivery boy based on the payload's mobile number
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('is_delete', 0)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $isStatusActive = filter_var($data['isStatusActive'], FILTER_VALIDATE_BOOLEAN);
        $workingStatus = $isStatusActive ? 0 : 1;

        // Update the delivery boy's active status
        $updateStatus = $deliveryBoyModel
            ->update($deliveryBoy['id'], ['a_status' => $workingStatus]);

        if ($updateStatus) {
            return $this->respond([
                'status' => 200,
                'result' => 'true',
                'message' => 'Status Changed Successfully'
            ]);
        } else {
            return $this->respond([
                'status' => 500,
                'result' => 'false',
                'message' => 'Something Went Wrong!'
            ]);
        }
    }

    public function fetchProfile()
    {
        // Get JSON data from the request

        // Validate the authorization token
        $payload = $this->authorizedToken();

        // Initialize the DeliveryBoy model
        $deliveryBoyModel = new DeliveryBoyModel();

        // Find the delivery boy based on the payload's mobile number
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('is_delete', 0)
            ->where('mobile', $payload['mobile'])
            ->first();

        // Check if the delivery boy exists
        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        // Return the delivery boy profile
        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Delivery Boy profile fetched successfully',
            'data' => $deliveryBoy
        ]);
    }

    public function deleteAccount()
    {
        // Validate the authorization token
        $payload = $this->authorizedToken();

        // Initialize the DeliveryBoy model
        $deliveryBoyModel = new DeliveryBoyModel();

        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        $updateStatus = $deliveryBoyModel
            ->update($deliveryBoy['id'], ['is_delete' => 1, 'status' => 0, 'is_available' => 0, 'a_status' => 0,]);

        if ($updateStatus) {
            return $this->respond([
                'status' => 200,
                'result' => 'true',
                'message' => 'Account deleted Successfully'
            ]);
        } else {
            return $this->respond([
                'status' => 500,
                'result' => 'false',
                'message' => 'Something Went Wrong!'
            ]);
        }
    }

    public function updateProfile()
    {
        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        $updateData = [
            'name' => $data['name'] ?? $deliveryBoy['name'],
            'dob' => date('Y-m-d', strtotime($data['dob'])) ?? $deliveryBoy['dob'], // Assuming dob is a string (YYYY-MM-DD)
            'bank_account_number' => $data['bank_account_number'] ?? $deliveryBoy['bank_account_number'],
            'bank_name' => $data['bank_name'] ?? $deliveryBoy['bank_name'],
            'account_name' => $data['account_name'] ?? $deliveryBoy['account_name'],
            'ifsc_code' => $data['ifsc_code'] ?? $deliveryBoy['ifsc_code'],
        ];

        $updateProfile = $deliveryBoyModel
            ->update($deliveryBoy['id'], $updateData);

        if ($updateProfile) {
            return $this->respond([
                'status' => 200,
                'result' => 'true',
                'message' => 'Profile updated Successfully'
            ]);
        } else {
            return $this->respond([
                'status' => 500,
                'result' => 'false',
                'message' => 'Something Went Wrong!'
            ]);
        }
    }

    public function fetchOrderStatusList()
    {
        $payload = $this->authorizedToken();

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderStatusListsModel = new OrderStatusListsModel();

        $orderStatusList = $orderStatusListsModel->select('id, status')->findAll();

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Order Status List',
            'data' => $orderStatusList
        ]);
    }

    public function fetchDeliverySettings()
    {
        return $this->respond([
            'deliverySettings' => $this->deliverySettings,
            'countrySettings' => $this->country
        ]);
    }

    public function fetchOrderList()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderModel = new OrderModel();
        $orderProductModel = new OrderProductModel();
        $orders = [];

        if (!isset($data['orderStatus']) || is_null($data['orderStatus']) || $data['orderStatus'] == 0 || ($data['orderStatus'] ?? 0) == 0) {

            if (isset($data['day'])) {
                $orderModel->where('DATE(orders.delivery_date)', $data['day']);
            }

            if (isset($data['uptoOrderStatus']) && $data['uptoOrderStatus'] != 0) {
                $orderModel->where('orders.status <=', $data['uptoOrderStatus']);
            }

            $orders = $orderModel
                ->select('orders.*,  order_status_lists.status AS status_name, order_status_lists.app_text_color AS text_color, order_status_lists.app_bg_color AS bg_color')
                ->join('order_status_lists', 'orders.status = order_status_lists.id', 'left')
                ->where('orders.delivery_boy_id', $deliveryBoy['id'])
                ->orderBy('orders.id', 'DESC')
                ->findAll();

            foreach ($orders as &$order) {
                $orderProducts = $orderProductModel
                    ->select('order_products.*, product.main_img')
                    ->join('product', 'order_products.product_id = product.id', 'left')
                    ->join('order_return_request', 'order_products.id = order_return_request.order_products_id AND order_return_request.status = 4', 'left')
                    ->where('order_products.order_id', $order['id'])
                    ->where('order_return_request.order_products_id IS NULL') // Exclude products with status = 4
                    ->findAll();

                // Attach orderProducts to the respective order
                $order['orderProducts'] = $orderProducts;
            }
        } else {
            // Fetch orders for the delivery boy with specific status
            $orders = $orderModel
                ->select('orders.*, order_status_lists.status as status_name')
                ->join('order_status_lists', 'orders.status = order_status_lists.id', 'left')
                ->where('orders.delivery_boy_id', $deliveryBoy['id'])
                ->where('orders.status', $data['orderStatus'])
                ->orderBy('orders.id', 'DESC')
                ->findAll();

            foreach ($orders as &$order) {
                $orderProducts = $orderProductModel
                    ->select('order_products.*, product.main_img')
                    ->join('product', 'order_products.product_id = product.id', 'left')
                    ->join('order_return_request', 'order_products.id = order_return_request.order_products_id AND order_return_request.status = 4', 'left')
                    ->where('order_products.order_id', $order['id'])
                    ->where('order_return_request.order_products_id IS NULL') // Exclude products with status = 4
                    ->findAll();

                // Attach orderProducts to the respective order
                $order['orderProducts'] = $orderProducts;
            }
        }



        // Prepare the response
        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Order List',
            'data' => $orders
        ]);
    }

    public function calculationStats()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $startOfDay = date('Y-m-d 00:00:00');
        $endOfDay = date('Y-m-d 23:59:59');

        $orderModel = new OrderModel();
        $todayPendingOrder = $orderModel
            ->select('orders.id')
            ->join('order_status_lists', 'orders.status = order_status_lists.id', 'left')
            ->where('DATE(orders.delivery_date)', date('Y-m-d'))
            ->where('orders.status <=', 5)
            ->where('orders.delivery_boy_id', $deliveryBoy['id'])
            ->countAllResults();

        $todayAllOrder = $orderModel
            ->select('orders.id')
            ->join('order_status_lists', 'orders.status = order_status_lists.id', 'left')
            ->where('DATE(orders.delivery_date)', date('Y-m-d'))
            ->where('orders.delivery_boy_id', $deliveryBoy['id'])
            ->countAllResults();

        $deliveryBoyTransactionModel = new DeliveryBoyTransactionModel();

        $deliveryBoyTransaction = $deliveryBoyTransactionModel
            ->select('SUM(amount) as dailyCollection')
            ->where('type', 'credit')
            ->where('created_at >=', $startOfDay)
            ->where('created_at <=', $endOfDay)
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->first();

        $dailyCollection = $deliveryBoyTransaction['dailyCollection'] ?? 0;


        $deliveryBoyFundTransferModel = new DeliveryBoyFundTransferModel();
        $totalEarningResult = $deliveryBoyFundTransferModel
            ->select('SUM(amount) as totalEarning')
            ->where('type', 'credit')
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->first();

        $totalEarning = $totalEarningResult['totalEarning'] ?? 0;

        $todayEarningResult = $deliveryBoyFundTransferModel
            ->select('SUM(amount) as todayEarning')
            ->where('type', 'credit')
            ->where('created_at >=', $startOfDay)
            ->where('created_at <=', $endOfDay)
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->first();

        $todayEarning = $todayEarningResult['todayEarning'] ?? 0;

        $orderReturnRequestModel = new OrderReturnRequestModel();
        $todayReturnOrder = $orderReturnRequestModel
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->where('status', 2)
            ->where('created_at >=', $startOfDay)
            ->where('created_at <=', $endOfDay)
            ->countAllResults();

        $totalReturnItemHave = $orderReturnRequestModel
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->where('status', 4)
            ->countAllResults();

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'dailyCollection' => $dailyCollection,
            'cashBalance' => $deliveryBoy['cash_collection_amount'],
            'todayEarning' => $todayEarning,
            'totalEarning' => $totalEarning,
            'todayPendingOrder' => $todayPendingOrder,
            'todayAllOrder' => $todayAllOrder,
            'todayReturnOrder' => $todayReturnOrder,
            'totalReturnItemHave' => $totalReturnItemHave
        ]);
    }

    public function fetchOrderDetails()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderModel = new OrderModel();

        $order = $orderModel
            ->select('orders.*, orders.status as orders_status, order_status_lists.status AS status_name, order_status_lists.app_text_color AS text_color, order_status_lists.app_bg_color AS bg_color, payment_method.title as payment_method_title, user.*, address.*')
            ->join('order_status_lists', 'orders.status = order_status_lists.id', 'left')
            ->join('payment_method', 'orders.payment_method_id = payment_method.id', 'left')
            ->join('user', 'orders.user_id = user.id', 'left')
            ->join('address', 'orders.address_id = address.id', 'left')
            ->where('orders.delivery_boy_id', $deliveryBoy['id'])
            ->where('orders.id', $data['id'])->first();

        $orderProductModel = new OrderProductModel();

        $orderProducts = $orderProductModel
            ->select('order_products.*, product.main_img, seller.store_name, seller.logo, seller.map_address, seller.latitude, seller.longitude, seller.mobile,')
            ->join('product', 'order_products.product_id = product.id', 'left')
            ->join('seller', 'order_products.seller_id = seller.id', 'left')
            ->join('order_return_request', 'order_products.id = order_return_request.order_products_id AND order_return_request.status = 4', 'left')
            ->where('order_products.order_id', $data['id'])
            ->where('order_return_request.order_products_id IS NULL') // Exclude products with status = 4
            ->findAll();


        $orderProductsWithSellers = $orderProductModel
            ->select('order_products.*, product.main_img, seller.store_name, seller.logo, seller.map_address, seller.latitude, seller.longitude, seller.mobile')
            ->join('product', 'order_products.product_id = product.id', 'left')
            ->join('seller', 'order_products.seller_id = seller.id', 'left')
            ->join('order_return_request', 'order_products.id = order_return_request.order_products_id AND order_return_request.status = 4', 'left')
            ->where('order_products.order_id', $data['id'])
            ->where('order_return_request.order_products_id IS NULL') // Exclude products with status = 4
            ->findAll();


        $sellersWithProducts = [];

        $subTotal = 0;

        foreach ($orderProductsWithSellers as $orderProduct) {
            // Check if seller already exists in the array
            if (!isset($sellersWithProducts[$orderProduct['seller_id']])) {
                // Add seller details if not already added
                $sellersWithProducts[$orderProduct['seller_id']] = [
                    'seller_details' => [
                        'store_name' => $orderProduct['store_name'],
                        'logo' => $orderProduct['logo'],
                        'map_address' => $orderProduct['map_address'],
                        'latitude' => $orderProduct['latitude'],
                        'longitude' => $orderProduct['longitude'],
                        'mobile' => $orderProduct['mobile'],
                    ],
                    'products' => []
                ];
            }

            // Add product to the respective seller's products list
            $sellersWithProducts[$orderProduct['seller_id']]['products'][] = [
                'product_id' => $orderProduct['product_id'],
                'product_name' => $orderProduct['product_name'],
                'product_variant_name' => $orderProduct['product_variant_name'],
                'quantity' => $orderProduct['quantity'],
                'price' => $orderProduct['price'],
                'discounted_price' => $orderProduct['discounted_price'],
                'tax_amount' => $orderProduct['tax_amount'],
                'tax_percentage' => $orderProduct['tax_percentage'],
                'discount' => $orderProduct['discount'],
                'main_img' => $orderProduct['main_img']
            ];

            $priceToUse = isset($orderProduct['discounted_price']) && $orderProduct['discounted_price'] > 0
                ? $orderProduct['discounted_price']
                : $orderProduct['price'];
            $subTotal += $orderProduct['quantity'] * $priceToUse;
        }



        $delivery_boy_commission = 0;

        if ($deliveryBoy['bonus_type'] == 1) {
            // Calculate the commission based on the percentage
            $commission = ($subTotal * $deliveryBoy['bonus_percentage']) / 100;

            // Ensure the commission is within the min and max bounds
            if ($commission < $deliveryBoy['bonus_min_amount']) {
                $delivery_boy_commission = $deliveryBoy['bonus_min_amount']; // Assign min amount
            } elseif ($commission > $deliveryBoy['bonus_max_amount']) {
                $delivery_boy_commission = $deliveryBoy['bonus_max_amount']; // Assign max amount
            } else {
                $delivery_boy_commission = $commission; // Assign calculated commission
            }
        }

        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'order' => $order,
            'orderProducts' => $orderProducts,
            'sellersWithProducts' => $sellersWithProducts,
            'deliveryBoyCommission' => $delivery_boy_commission,
        ]);
    }

    public function placeOrderDelivery()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);
        helper('firebase_helper');

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderModel = new OrderModel();

        $order = $orderModel->where('id', $data['id'])->first();

        if (!empty($this->deliverySettings['order_delivery_verification'])) {
            if (!empty($data['otp'])) {
                $isValidOtp = $orderModel
                    ->select('id')
                    ->where('id', $data['id'])
                    ->where('order_delivery_otp', $data['otp'])
                    ->first();

                if (!$isValidOtp) {
                    return $this->respond([
                        'status' => 404,
                        'result' => false,
                        'message' => 'Invalid delivery OTP. Please enter the correct OTP.',
                    ]);
                }
            } else {
                return $this->respond([
                    'status' => 404,
                    'result' => false,
                    'message' => 'OTP is required. Please enter a valid 4-digit delivery OTP.',
                ]);
            }
        }


        if ((int)$this->deliverySettings['delivery_boy_cash_in_hand'] && $order['payment_method_id'] == 1) {
            // Check if cash collection amount exceeds the allowed limit
            if ($deliveryBoy['cash_collection_amount'] > $this->deliverySettings['delivery_boy_maximum_cash_in_hand']) {
                return $this->respond([
                    'status' => 402,
                    'result' => 'false',
                    'message' => 'Cash Collection limit exceeded. Unable to deliver the order.',
                ]);
            }
        }


        if ($this->deliverySettings['order_delivery_verification']) {
            if (!empty($data['otp'])) {
                // Check if the OTP is valid
                $isValidOtp = $orderModel
                    ->select('id')
                    ->where('id', $data['id'])
                    ->where('order_delivery_otp', $data['otp'])
                    ->first();

                if ($isValidOtp) {
                    // Update order data if OTP is valid
                    $orderData = [
                        'status' => 6,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'order_delivery_otp_verification' => 1,
                    ];

                    $orderModel->update($data['id'], $orderData); // Use direct ID for update
                }
            }
        } else {
            // Update order without OTP verification
            $orderData = [
                'status' => 6,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $orderModel->update($data['id'], $orderData); // Use direct ID for update
        }


        if ($orderModel->db->affectedRows() > 0) {
            $orderStatusesModel = new OrderStatusesModel();

            $orderStatusesData =  [
                'orders_id' => $data['id'],
                'status' => 6,
                'created_by' => $deliveryBoy['id'],
                'user_type' => 'Delivery Boy',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $orderStatusesModel->insert($orderStatusesData);

            // for seller
            $sellerModel = new SellerModel();
            $sellerWalletTransactionModel = new SellerWalletTransactionModel();

            // Fetch order products based on the given order ID
            $orderProductModel = new OrderProductModel();
            $orderProducts = $orderProductModel->where('order_id', $data['id'])->findAll();

            // Ensure there are order products
            if (!empty($orderProducts)) {
                foreach ($orderProducts as $orderProduct) {
                    $seller = $sellerModel->select('commission, balance')->where('id', $orderProduct['seller_id'])->first();

                    // Determine the amount
                    $amount = ($orderProduct['discounted_price'] > 0
                        ? $orderProduct['discounted_price']
                        : $orderProduct['price']) * $orderProduct['quantity'];

                    $commission_amt = round(($amount * (100 - $seller['commission'])) / 100, 2);

                    // Prepare the data for insertion
                    $transactionData = [
                        'order_id' => $orderProduct['order_id'],
                        'order_products_id' => $orderProduct['id'],
                        'seller_id' => $orderProduct['seller_id'],
                        'type' => 'credit',
                        'amount' => $commission_amt,
                        'status' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'this_is_request' => 0,
                        'transaction_done_by' => 0,
                    ];

                    // Insert the data into the seller_wallet_transaction table
                    $sellerWalletTransactionModel->insert($transactionData);

                    $balance = round($seller['balance'] + $commission_amt, 2);

                    $sellerModel->update($orderProduct['seller_id'], ['balance' => $balance]);
                }
            }

            // for Delivery Boy
            if ($this->deliverySettings['delivery_boy_bonus_setting'] == 1) {

                if ($deliveryBoy['bonus_type'] == 1) {
                    $deliveryBoyFundTransferModel = new DeliveryBoyFundTransferModel();

                    $deliveryBoyFundTransfer = $deliveryBoyFundTransferModel
                        ->where('delivery_boy_id', $deliveryBoy['id'])
                        ->orderBy('id', 'DESC')
                        ->first();

                    if ($deliveryBoyFundTransfer) {

                        $newOpeningBalance = $deliveryBoyFundTransfer['closing_balance'];
                        $newClosingBalance = $newOpeningBalance + $data['deliveryBoyCommission'];

                        $deliveryBoyFundTransferData = [
                            'delivery_boy_id' => $deliveryBoy['id'],
                            'order_id' => $data['id'],
                            'type' => 'credit',
                            'opening_balance' => $newOpeningBalance,
                            'closing_balance' => $newClosingBalance,
                            'amount' => $data['deliveryBoyCommission'],
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $deliveryBoyFundTransferModel->insert($deliveryBoyFundTransferData);

                        $deliveryBoyModel->update($deliveryBoy['id'], ['balance' => $newClosingBalance]);
                    } else {
                        $deliveryBoyFundTransferData = [
                            'delivery_boy_id' => $deliveryBoy['id'],
                            'order_id' => $data['id'],
                            'type' => 'credit',
                            'opening_balance' => 0.00,
                            'closing_balance' => $data['deliveryBoyCommission'],
                            'amount' => $data['deliveryBoyCommission'],
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $deliveryBoyFundTransferModel->insert($deliveryBoyFundTransferData);

                        $deliveryBoyModel->update($deliveryBoy['id'], ['balance' => $data['deliveryBoyCommission']]);
                    }
                }
            }
            if ($order['payment_method_id'] == 1) {
                //cash can be collected
                $totalCashCollectionAmount = $deliveryBoy['cash_collection_amount'] + $data['grandTotal'];
                $deliveryBoyModel->update($deliveryBoy['id'], ['cash_collection_amount' => $totalCashCollectionAmount]);

                $deliveryBoyTransactionData = [
                    'user_id' => $order['user_id'],
                    'order_id' => $data['id'],
                    'delivery_boy_id' => $deliveryBoy['id'],
                    'type' => 'credit',
                    'amount' => $data['grandTotal'],
                    'transaction_date' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $deliveryBoyTransactionModel = new DeliveryBoyTransactionModel();
                $deliveryBoyTransactionModel->insert($deliveryBoyTransactionData);
            }
            $deviceTokenModel = new DeviceTokenModel();


            $userToken = $deviceTokenModel->where('user_type', 2)->where('user_id', $order['user_id'])->orderBy('id', 'desc')->first();
            $dataForNotification = [
                'screen' => 'Notification',
            ];
            $OrderStatusListsModel = new OrderStatusListsModel();

            $statusDetails =  $OrderStatusListsModel->select('status')->where('id', 6)->first();
            if (isset($userToken['app_key'])) {
                sendFirebaseNotification($userToken['app_key'], 'Your order status updated to ' . $statusDetails['status'], 'For order id ' . $data['id'], $dataForNotification);
            }
            return $this->respond([
                'status' => 200,
                'result' => 'true',
                'message' => 'Order Delivered Successfully'
            ]);
        } else {
            return $this->respond([
                'status' => 402,
                'result' => 'false',
                'message' => 'Unable to delivered order'
            ]);
        }
    }


    public function fetchReturnOrderList()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();
        $orderReturnRequests = $orderReturnRequestModel
            ->select('order_return_request.order_id as id, 
              orders.order_id as order_id, 
              COUNT(CASE WHEN order_return_request.status = 2 THEN 1 END) as status_2_count, 
              COUNT(CASE WHEN order_return_request.status = 5 THEN 1 END) as status_5_count, 
              COUNT(CASE WHEN order_return_request.status = 4 THEN 1 END) as status_4_count')
            ->join('orders', 'orders.id = order_return_request.order_id', 'left')
            ->where('order_return_request.delivery_boy_id', $deliveryBoy['id'])
            ->groupBy('order_return_request.order_id')
            ->findAll();


        return $this->respond([
            'status' => 200,
            'result' => 'true',
            'message' => 'Order List',
            'data' => $orderReturnRequests
        ]);
    }

    public function fetchReturnOrderDetails()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();
        $orderProductsModel = new OrderProductModel();
        $productModel = new ProductModel();
        $sellerModel = new SellerModel();
        $ordersModel = new OrderModel();
        $addressModel = new AddressModel();

        $order_id = $data['order_id'];

        $returnRequests = $orderReturnRequestModel
            ->where('order_id', $order_id)
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->findAll();

        // Initialize arrays to store seller details and return items
        $returnOrderItemsBySeller = [];

        $is_customer_details_show = 0;

        // Loop through return requests and group by seller
        foreach ($returnRequests as $returnRequest) {
            // Get product details
            $orderProduct = $orderProductsModel->find($returnRequest['order_products_id']);
            $product = $productModel->where('id', $orderProduct['product_id'])->first();

            // Get seller details
            $seller = $sellerModel->where('id', $orderProduct['seller_id'])->first();

            // Get order address details
            $order = $ordersModel->where('id', $order_id)->first();
            $address = $addressModel->find($order['address_id']);

            // Prepare return order item data
            $returnOrderItem = [
                'return_request_id' => $returnRequest['id'],
                'order_products_id' => $returnRequest['order_products_id'],
                'reason' => $returnRequest['reason'],
                'status' => $returnRequest['status'],
                'delivery_boy_id' => $returnRequest['delivery_boy_id'],
                'product' => [
                    'product_name' => $orderProduct['product_name'],
                    'product_variant_name' => $orderProduct['product_variant_name'],
                    'quantity' => $orderProduct['quantity'],
                    'main_img' => $product['main_img']
                ],
            ];

            if ($returnRequest['status'] == 2 && $is_customer_details_show == 0) {
                $is_customer_details_show = 1; // if any status is pending then show customer details
            }

            // Group the return order items by seller_id
            if (!isset($returnOrderItemsBySeller[$seller['id']])) {
                // If no entries for this seller, create a new entry
                $returnOrderItemsBySeller[$seller['id']] = [
                    'seller' => [
                        'name' => $seller['name'],
                        'store_name' => $seller['store_name'],
                        'store_address' => $seller['store_address'],
                        'map_address' => $seller['map_address'],
                        'latitude' => $seller['latitude'],
                        'longitude' => $seller['longitude'],
                    ],
                    'returnOrderItems' => []
                ];
            }

            // Add the return order item to the seller's returnOrderItems list
            $returnOrderItemsBySeller[$seller['id']]['returnOrderItems'][] = $returnOrderItem;
        }

        $response = [
            'order_id' => $order_id,
            'user_details' => [
                'user_id' => $address['user_id'],
                'user_name' => $address['user_name'],
                'user_mobile' => $address['user_mobile'],
                'latitude' => $address['latitude'],
                'longitude' => $address['longitude'],
                'map_address' => $address['map_address'],
                'is_customer_details_show' => $is_customer_details_show
            ],
            'sellers' => array_values($returnOrderItemsBySeller),
        ];

        return $this->response->setJSON($response);
    }

    public function confirmReturnItem()
    {
        date_default_timezone_set($this->timeZone['timezone']);

        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel
            ->where('status', 1)
            ->where('is_available', 1)
            ->where('mobile', $payload['mobile'])
            ->first();

        if (!$deliveryBoy) {
            return $this->respond([
                'status' => 404,
                'result' => 'false',
                'message' => 'Delivery Boy not found'
            ]);
        }

        $orderReturnRequestModel = new OrderReturnRequestModel();

        $orderReturnRequestData = [
            'status' => 4,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        // Perform the update query
        $updated = $orderReturnRequestModel
            ->where('delivery_boy_id', $deliveryBoy['id'])
            ->where('order_id', $data['order_id'])
            ->update($data['order_return_request_id'], $orderReturnRequestData);

        // Check if the update was successful
        if ($updated) {
            return $this->respond([
                'status' => 200,
                'result' => 'true',
            ]);
        } else {
            return $this->respond([
                'status' => 400,
                'result' => 'false',
                'message' => 'Unable to update order return request. Please verify the provided data.',
            ]);
        }
    }

    public function fetchPrivacyPolicy()
    {
        $settings = $this->deliverySettings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['delivery_app_privacy_policy']]);
    }

    public function fetchAboutUs()
    {
        $settings = $this->deliverySettings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['delivery_app_about']]);
    }

    public function fetchContactUs()
    {
        $settings = $this->deliverySettings;

        $output = [
            "business_name" => $settings['business_name'],
            "logo" => base_url($settings['logo']),
            "phone" => $settings['phone'],
            "email" => $settings['email'],

        ];

        return $this->response->setJSON(['status' => 'success', 'data' => $output]);
    }


    public function fetchTermsAndCondition()
    {
        $settings = $this->deliverySettings;
        return $this->response->setJSON(['status' => 'success', 'data' => $settings['delivery_app_terms_policy']]);
    }

    public function updateDeliveryBoyLocation()
    {
        $payload = $this->authorizedToken();
        $data = $this->request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();

        // Find the delivery boy based on the payload's mobile number
        $deliveryBoy = $deliveryBoyModel
            // ->where('status', 1)
            // ->where('is_available', 1)
            ->where('is_delete', 0)
            ->where('mobile', $payload['mobile'])
            ->first();

        $deliveryTrackingModel = new DeliveryTrackingModel();
        $data = [
            'delivery_id' => $deliveryBoy['id'],
            'order_id' => $data['order_id'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'last_updated' => date("Y-m-d H:i:s"),
            'heading' => $data['heading']
        ];

        $existing = $deliveryTrackingModel->where('delivery_id', $deliveryBoy['id'])->where('order_id', $data['order_id'])->first();
        if ($existing) {
            $deliveryTrackingModel->update($existing['id'], $data);
            return $this->respond([
                "status" => 200,
                "result" => "true",
                "msg"    =>  'Status updated'
            ]);
        } else {
            $deliveryTrackingModel->insert($data);
            return $this->respond([
                "status" => 200,
                "result" => "true",
                "msg"    =>  'Status added'
            ]);
        }
    }
    public function fetchCities()
    {
        $cityModel = new CityModel();
        $cities = array();

        $cities = $cityModel->select('id, name')->where('is_delete', 0)->orderBy('name', 'ASC')->findAll();

        return $this->respond([
            'status' => 200,
            'msg' => 'Cities fetched successfully',
            'data' => $cities
        ]);
    }
    public function registerDeliveryBoy()
    {
        $request = service('request');
        $decodeData = $request->getJSON(true);

        $deliveryBoyModel = new DeliveryBoyModel();
        $deliveryBoy = $deliveryBoyModel->where('mobile', $decodeData['mobile'])
            ->first();

        if ($deliveryBoy) {
            return $this->respond([
                'status' => 200,
                'result' => 'true',
                'message' => 'This Number already Register'
            ]);
        }

        $data = [
            'admin_id' => 0,
            'city_id' => $decodeData['city'],
            'name' => $decodeData['name'],
            'mobile' => $decodeData['mobile'],
            'password' => password_hash($decodeData['password'], PASSWORD_BCRYPT),
            'status' => 0,
            'is_delete' => 0,
            'a_status' => 0,
            'address' => $decodeData['address'],
            'cash_collection_amount' => 0.00
        ];


        if ($deliveryBoyModel->insert($data)) {
            $insertedId = $deliveryBoyModel->getInsertID();
            $deviceTokenModel = new DeviceTokenModel();
            $deviceTokenModel->insert(['user_type' => 3, 'user_id' => $insertedId, 'app_key' => $decodeData['fcmToken']]);

            return $this->respond([
                'status' => 200,
                'result' => 'true',
                'message' => 'Successfully Registered'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 500,
                'message' => 'Unable to Register user'
            ]);
        }
    }

    public function fetchLanguageList() {
        
        $languageModel = new LanguageModel();

        $languageList = $languageModel->where('is_active', 1)->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $languageList]);
    }
}
