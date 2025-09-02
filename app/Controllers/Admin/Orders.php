<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CountryModel;
use App\Models\DeliveryBoyFundTransferModel;
use App\Models\DeliveryBoyModel;
use App\Models\DeliveryBoyTransactionModel;
use App\Models\DeviceTokenModel;
use App\Models\OrderModel;
use App\Models\OrderProductModel;
use App\Models\OrderStatusesModel;
use App\Models\OrderStatusListsModel;
use App\Models\SellerModel;
use App\Models\SellerWalletTransactionModel;
use App\Models\SettingsModel;
use App\Models\TimeslotModel;

header('Content-Type: text/html; charset=utf-8');

use Dompdf\Dompdf;
use Dompdf\Options;

class Orders extends BaseController
{
    public function index()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('manage-orders')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $sellerModel = new SellerModel();
            $data['sellers'] = $sellerModel->where('status', 1)->where('is_delete', 0)->findAll();
            $orderStatusListsModel = new OrderStatusListsModel();
            $data['orderStatusLists'] = $orderStatusListsModel->findAll();
            $TimeslotModel = new TimeslotModel();

            $data['timeslots'] = $TimeslotModel->findAll();

            return view('/orders/orders', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function list()
    {
        if (!session()->has('user_id') || session('account_type') !== 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('manage-orders')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $orderModel = new OrderModel();
        $orderProductModel = new OrderProductModel();
        $countryModel = new CountryModel();
        $deliveryBoyModel = new DeliveryBoyModel();
        $country = $countryModel->where('is_active', 1)->first();

        // Get input filters
        $orderDate = $this->request->getPost('order_date');
        $seller = $this->request->getPost('seller');
        $status = $this->request->getPost('status');

        // Handle default order date
        if (empty($orderDate)) {
            $today = date('Y-m-d');
            $orderDate = "$today - $today";
        }
        $dates = explode(' - ', $orderDate);

        // Base query
        $builder = $orderModel->select(
            'orders.id as order_id, orders.order_id as user_order_id, orders.user_id, orders.additional_charge, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount, orders.delivery_charge, orders.coupon_amount,  orders.order_date, orders.delivery_date, orders.timeslot, orders.delivery_boy_id, orders.transaction_id, orders.status, GROUP_CONCAT(DISTINCT seller.store_name) as seller_names, user.name as user_name, user.mobile as user_mobile, address.address, address.city_id, address.area, address.city, address.state, address.pincode, delivery_boy.name as delivery_boy_name, delivery_boy.mobile as delivery_boy_mobile, order_status_lists.status as order_status, order_status_lists.color as order_status_color'
        )
            ->join('order_products', 'order_products.order_id = orders.id', 'left')
            ->join('delivery_boy', 'delivery_boy.id = orders.delivery_boy_id', 'left')
            ->join('order_status_lists', 'order_status_lists.id = orders.status', 'left')
            ->join('user', 'user.id = orders.user_id', 'left')
            ->join('address', 'address.id = orders.address_id', 'left')
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->groupBy('orders.id');

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(orders.order_date) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(orders.order_date) <=', date('Y-m-d', strtotime($dates[1])));
        }
        if (!empty($seller)) {
            $builder->where('order_products.seller_id', $seller);
        }
        if (!empty($status)) {
            $builder->where('orders.status', $status);
        }

        // Fetch data
        $query = $builder->get();
        $orders = $query->getResultArray();

        // Prepare output
        $output['data'] = [];
        foreach ($orders as $index => $order) {

            $selectSubtotal = $orderProductModel->select('SUM(CASE 
            WHEN order_products.discounted_price = 0 THEN order_products.price * order_products.quantity 
            ELSE order_products.discounted_price * order_products.quantity 
        END) as subtotal')
                ->join('order_return_request', 'order_return_request.order_products_id = order_products.id AND order_return_request.status IN (2, 4)', 'left')
                ->where('order_return_request.id IS NULL') // Exclude returned items
                ->where('order_products.order_id', $order['order_id']) // No return request
                ->first();
            $address_details = $order['address'] . ", " . $order['area'] . ", " . $order['city'] . ", " . $order['state'] . "-" . $order['pincode'];
            $user_details = $order['user_name'] . "<br>" . $order['user_mobile'];
            $amount = $selectSubtotal['subtotal'] + $order['tax'] + $order['additional_charge']  - $order['used_wallet_amount'] + $order['delivery_charge'] - $order['coupon_amount'];
            $delivery_boy_lists =  $deliveryBoyModel->select('id, name')->where('city_id', $order['city_id'])->where('status', 1)->where('a_status', 1)->where('is_delete', 0)->findAll();

            if (empty($delivery_boy_lists)) {
                $deliveryboy = '<li><a class="dropdown-item" href="#">No delivery boy found</a></li>';
            } else {
                $deliveryboy = '';
            }
            foreach ($delivery_boy_lists as $delivery_boy_list) {
                $deliveryboy .= '<li><a class="dropdown-item" href="#" onclick="assignDeliveryBoy(' . $order['order_id'] . ', ' . $delivery_boy_list['id'] . ')">' . $delivery_boy_list['name'] . '</a></li>';
            };


            if ($order['delivery_boy_id']) {
                $builder->where('orders.status', $status);
                if ($order['status'] != 6 && $order['status'] != 7 && $order['status'] != 8) {
                    $delivery_boy_status = $order['delivery_boy_name'] . '<br>
                    <div class="btn-group mx-2">
                        <a href="#!"  class="btn btn-primary dropdown-toggle btn-xs"  data-toggle="dropdown" aria-expanded="false">Assign Delivery Boy</a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">'
                        . $deliveryboy . '
                        </ul> 
                    </div>';

                } else {
                    $delivery_boy_status = $order['delivery_boy_name'];
                }
            } else {
                if ($order['status'] != 6 && $order['status'] != 7 && $order['status'] != 8) {
                    $delivery_boy_status = '<span class="badge badge-danger">Not Assigned</span><br><div class="btn-group mx-2">
                        <a href="#!"  class="btn btn-primary dropdown-toggle btn-xs"  data-toggle="dropdown" aria-expanded="false">Assign Delivery Boy</a>
                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">'
                                . $deliveryboy . '
                            </ul> 
                        </div>';
                } else {
                    $delivery_boy_status = '<span class="badge badge-danger">Not Assigned</span>';
                }
            }
            if (empty($order['delivery_date'])) {
                if ($order['status'] != 6 && $order['status'] != 7 && $order['status'] != 8) {
                    $delivery_date = '<button class="btn btn-danger-light  btn-sm" onclick="assignDeliveryDate(' . $order['order_id'] . ')">Assign Delivery Date</button>';
                } else {
                    $delivery_date = '';
                }
            } else {
                $delivery_date = date('d-m-Y', strtotime($order['delivery_date']));
            }

            $output['data'][] = [
                $order['user_order_id'],
                $user_details,
                $address_details,
                $delivery_date,
                $order['order_date'],
                "<span class='badge {$order['order_status_color']}'>{$order['order_status']}</span>",
                $delivery_boy_status,
                $country['currency_symbol'] . " " . round($amount, 2),
                "<a data-tooltip='tooltip' target='_blank' title='View Order' href='" . base_url("admin/orders/view/{$order['order_id']}") . "' class='btn btn-primary-light  btn-xs'>
                    <i class='fi fi-tr-magnifying-glass-eye'></i></a>
                   <a type='button' data-tooltip='tooltip' title='Delete Order' onclick='deleteOrder({$order['order_id']})' class='btn btn-danger-light btn-xs'>
                    <i class='fi fi-tr-trash-xmark'></i></a>"
            ];
        }

        return $this->response->setJSON($output);
    }
    public function listOrderWithLimit($limit)
    {
        if (!session()->has('user_id') || session('account_type') !== 'Admin') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }
        if (!can_view('manage-orders')) {
            $output = ['success' => false, "message" => "Permission not allowed"];
            return $this->response->setJSON($output);
        }

        $orderModel = new OrderModel();
        $countryModel = new CountryModel();
        $country = $countryModel->where('is_active', 1)->first();

        // Get input filters
        $orderDate = $this->request->getPost('order_date');
        $seller = $this->request->getPost('seller');
        $status = $this->request->getPost('status');

        // Handle default order date
        if (empty($orderDate)) {
            $today = date('Y-m-d');
            $orderDate = "$today - $today";
        }
        $dates = explode(' - ', $orderDate);

        // Base query
        $builder = $orderModel->select(
            'orders.id as order_id, orders.user_id, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount, orders.delivery_charge, orders.coupon_amount,  orders.order_date, orders.delivery_date, orders.timeslot, orders.delivery_boy_id, orders.transaction_id, orders.status, GROUP_CONCAT(DISTINCT seller.store_name) as seller_names, user.name as user_name, user.mobile as user_mobile, address.address, address.area, address.city, address.state, address.pincode, delivery_boy.name as delivery_boy_name, delivery_boy.mobile as delivery_boy_mobile, order_status_lists.status as order_status, order_status_lists.color as order_status_color'
        )
            ->join('order_products', 'order_products.order_id = orders.id', 'left')
            ->join('delivery_boy', 'delivery_boy.id = orders.delivery_boy_id', 'left')
            ->join('order_status_lists', 'order_status_lists.id = orders.status', 'left')
            ->join('user', 'user.id = orders.user_id', 'left')
            ->join('address', 'address.id = orders.address_id', 'left')
            ->join('seller', 'seller.id = order_products.seller_id', 'left')
            ->groupBy('orders.id')
            ->orderBy('orders.id', 'desc')
            ->limit((int)$limit);

        // Apply filters
        if (!empty($dates)) {
            $builder->where('DATE(orders.order_date) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(orders.order_date) <=', date('Y-m-d', strtotime($dates[1])));
        }
        if (!empty($seller)) {
            $builder->where('order_products.seller_id', $seller);
        }
        if (!empty($status)) {
            $builder->where('orders.status', $status);
        }

        // Fetch data
        $query = $builder->get();
        $orders = $query->getResultArray();

        // Prepare output
        $output['data'] = [];
        foreach ($orders as $order) {
            $user_details = $order['user_name'] . "<br>" . $order['user_mobile'];
            $amount = $order['subtotal'] + $order['tax'] - $order['used_wallet_amount'] + $order['delivery_charge'] - $order['coupon_amount'];
            $output['data'][] = [
                $order['order_id'],
                $user_details,
                $order['order_date'],
                "<span class='badge {$order['order_status_color']}'>{$order['order_status']}</span>",
                $country['currency_symbol'] . " " . round($amount, 2),
                "<a data-tooltip='tooltip' target='_blank' title='View Order' href='" . base_url("admin/orders/view/{$order['order_id']}") . "' class='btn btn-primary-light  btn-xs'>
                    <i class='fi fi-tr-magnifying-glass-eye'></i></a>"
            ];
        }

        return $this->response->setJSON($output);
    }

    public function view($order_id)
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('manage-orders')) {
                return redirect()->to('admin/permission-not-allowed');
            }

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $orderStatusListsModel = new OrderStatusListsModel();
            $data['status_list'] =  $orderStatusListsModel->findAll();

            $deliveryBoyModel = new DeliveryBoyModel();


            $orderModel = new OrderModel();
            $countryModel = new CountryModel();
            $data['country'] = $countryModel->where('is_active', 1)->first();
            $data['orderDetails'] = $orderModel->select(
                'orders.id as order_id,  orders.order_id as user_order_id, orders.user_id, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount,  orders.additional_charge, orders.note,
                    orders.delivery_charge, orders.coupon_amount,  orders.order_date, orders.delivery_date, 
                    orders.timeslot, orders.delivery_boy_id, orders.transaction_id, orders.status, user.name as user_name, 
                    user.mobile as user_mobile, user.email as user_email, address.address, address.area, address.city, address.city_id, address.state, address.pincode, 
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
            $data['delivery_boy_lists'] =  $deliveryBoyModel->select('id , name')->where('city_id', $data['orderDetails']['city_id'])->where('status', 1)->where('a_status', 1)->where('is_delete', 0)->findAll();

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
                ->where('order_products.order_id', $order_id)
                ->findAll();


            $data['subtotalOfOrder'] = $orderProductModel->select('SUM(CASE 
            WHEN order_products.discounted_price = 0 THEN order_products.price * order_products.quantity 
            ELSE order_products.discounted_price * order_products.quantity 
        END) as subtotal')
                ->join('order_return_request', 'order_return_request.order_products_id = order_products.id AND order_return_request.status IN (2, 4, 5)', 'left')
                ->where('order_return_request.id IS NULL') // Exclude returned items
                ->where('order_products.order_id', $order_id) // No return request
                ->first();

            $data['pendingOrders'] = $orderModel->getOrdersByStatus(1);

            return view('orders/orderDetails', $data);
        } else {
            return redirect()->to('admin/auth/login');
        }
    }
    public function downloadInvoice()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_view('manage-orders')) {
                return redirect()->to('admin/permission-not-allowed');
            }
            $order_id = $this->request->getPost('invoice');

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            $orderModel = new OrderModel();
            $countryModel = new CountryModel();
            $data['country'] = $countryModel->where('is_active', 1)->first();
            $data['orderDetails'] = $orderModel->select(
                'orders.id as order_id,  orders.order_id as user_order_id,  orders.user_id, orders.address_id, orders.subtotal, orders.tax, orders.used_wallet_amount, 
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
                ->where('order_products.order_id', $order_id)
                ->findAll();

            $cssPath = FCPATH . 'assets/dist/css/adminlte.min.css'; // Path to your external CSS file
            $css = file_get_contents($cssPath);
            $data['subtotalOfOrder'] = $orderProductModel->select('SUM(CASE 
            WHEN order_products.discounted_price = 0 THEN order_products.price * order_products.quantity 
            ELSE order_products.discounted_price * order_products.quantity 
        END) as subtotal')
                ->join('order_return_request', 'order_return_request.order_products_id = order_products.id AND order_return_request.status IN (2, 4, 5)', 'left')
                ->where('order_return_request.id IS NULL') // Exclude returned items
                ->where('order_products.order_id', $order_id) // No return request
                ->first();

            // Load the view into a variable and include inline styles
            $html = view('orders/invoice',  $data);
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
            $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parsing
            $options->set('isRemoteEnabled', true); // Enable loading images if required
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);

            // Load HTML into Dompdf
            $dompdf->loadHtml($html);

            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render the HTML as PDF
            $dompdf->render();

            // Output the generated PDF for download
            $this->response->setHeader('Content-Type', 'application/pdf');
            return $this->response->setBody($dompdf->output());
        } else {
            return redirect()->to('admin/auth/login');
        }
    }

    public function updateOrderStatus()
    {
        $session = session();
        date_default_timezone_set($this->timeZone['timezone']);
        helper('firebase_helper');

        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('manage-orders')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $order_id = $this->request->getPost('order_id');
            $status = $this->request->getPost('status');

            $orderModel = new OrderModel();

            $data = ['status' => $status];
            $updateOrder = $orderModel->set($data)->where('id', $order_id)->update();
            $selectOrderDetails = $orderModel->where('id', $order_id)->first();
            if ($updateOrder) {
                $OrderStatusesModel = new OrderStatusesModel();
                $OrderStatusListsModel = new OrderStatusListsModel();
                if ($status == 6) {
                    $deliveryBoyModel = new DeliveryBoyModel();

                    $deliveryBoy = $deliveryBoyModel
                        ->where('status', 1)
                        ->where('is_available', 1)
                        ->where('id', $selectOrderDetails['delivery_boy_id'])
                        ->first();

                    if (isset($deliveryBoy['id'])) {
                        $settingModel = new SettingsModel();
                        $appSetting = $settingModel->getSettings();

                        if ($appSetting['delivery_boy_bonus_setting'] == 1) {

                            if ($deliveryBoy['bonus_type'] == 1) {
                                $deliveryBoyFundTransferModel = new DeliveryBoyFundTransferModel();

                                $deliveryBoyFundTransfer = $deliveryBoyFundTransferModel
                                    ->where('delivery_boy_id', $deliveryBoy['id'])
                                    ->orderBy('id', 'DESC')
                                    ->first();

                                if ($deliveryBoyFundTransfer) {
                                    $calculatedBonus = ($selectOrderDetails['subtotal'] * $deliveryBoy['bonus_percentage']) / 100;

                                    // Ensure bonus is within the min and max limits
                                    if ($calculatedBonus < $deliveryBoy['bonus_min_amount']) {
                                        $calculatedBonus =  $deliveryBoy['bonus_min_amount'];
                                    } elseif ($calculatedBonus > $deliveryBoy['bonus_max_amount']) {
                                        $calculatedBonus =  $deliveryBoy['bonus_max_amount'];
                                    }

                                    $newOpeningBalance = $deliveryBoyFundTransfer['closing_balance'];
                                    $newClosingBalance = $newOpeningBalance + $calculatedBonus;

                                    $deliveryBoyFundTransferData = [
                                        'delivery_boy_id' => $deliveryBoy['id'],
                                        'order_id' => $selectOrderDetails['id'],
                                        'type' => 'credit',
                                        'opening_balance' => $newOpeningBalance,
                                        'closing_balance' => $newClosingBalance,
                                        'amount' => $calculatedBonus,
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];
                                    $deliveryBoyFundTransferModel->insert($deliveryBoyFundTransferData);

                                    $deliveryBoyModel->update($deliveryBoy['id'], ['balance' => $newClosingBalance]);
                                } else {
                                    $calculatedBonus = ($selectOrderDetails['subtotal'] * $deliveryBoy['bonus_percentage']) / 100;

                                    // Ensure bonus is within the min and max limits
                                    if ($calculatedBonus < $deliveryBoy['bonus_min_amount']) {
                                        $calculatedBonus =  $deliveryBoy['bonus_min_amount'];
                                    } elseif ($calculatedBonus > $deliveryBoy['bonus_max_amount']) {
                                        $calculatedBonus =  $deliveryBoy['bonus_max_amount'];
                                    }


                                    $deliveryBoyFundTransferData = [
                                        'delivery_boy_id' => $deliveryBoy['id'],
                                        'order_id' => $selectOrderDetails['id'],
                                        'type' => 'credit',
                                        'opening_balance' => 0.00,
                                        'closing_balance' => $calculatedBonus,
                                        'amount' => $calculatedBonus,
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];
                                    $deliveryBoyFundTransferModel->insert($deliveryBoyFundTransferData);

                                    $deliveryBoyModel->update($deliveryBoy['id'], ['balance' => $calculatedBonus]);
                                }

                                if ($selectOrderDetails['payment_method_id'] == 1) {
                                    //cash can be collected
                                    $totalCashCollectionAmount = $deliveryBoy['cash_collection_amount'] + $selectOrderDetails['subtotal'] + $selectOrderDetails['tax'] + $selectOrderDetails['delivery_charge'] + $selectOrderDetails['additional_charge'] - $selectOrderDetails['used_wallet_amount'];
                                    $deliveryBoyModel->update($deliveryBoy['id'], ['cash_collection_amount' => $totalCashCollectionAmount]);

                                    $deliveryBoyTransactionData = [
                                        'user_id' => $selectOrderDetails['user_id'],
                                        'order_id' => $selectOrderDetails['id'],
                                        'delivery_boy_id' => $deliveryBoy['id'],
                                        'type' => 'credit',
                                        'amount' => $selectOrderDetails['subtotal'] + $selectOrderDetails['tax'] + $selectOrderDetails['delivery_charge'] + $selectOrderDetails['additional_charge'] - $selectOrderDetails['used_wallet_amount'],
                                        'transaction_date' => date('Y-m-d H:i:s'),
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];

                                    $deliveryBoyTransactionModel = new DeliveryBoyTransactionModel();
                                    $deliveryBoyTransactionModel->insert($deliveryBoyTransactionData);
                                }
                            }
                        }
                    }

                    $sellerModel = new SellerModel();
                    $sellerWalletTransactionModel = new SellerWalletTransactionModel();
                    $orderProductModel = new OrderProductModel();
                    $orderProducts = $orderProductModel->where('order_id', $order_id)->findAll();

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
                }
                $userDetails = $orderModel->select('orders.user_id, user.name')
                    ->join('user', 'user.id = orders.user_id', 'left')
                    ->where('orders.id', $order_id)
                    ->first();
                $deviceTokenModel = new DeviceTokenModel();
                $userToken = $deviceTokenModel->where('user_type', 2)->where('user_id', $userDetails['user_id'])->orderBy('id', 'desc')->first();
                $dataForNotification = [
                    'screen' => 'Notification',
                ];
                $statusDetails =  $OrderStatusListsModel->select('status')->where('id', $status)->first();
                if (isset($userToken['app_key'])) {
                    $send_notification = false;
                    if ($status == 2 || $this->settings['notification_order_received_status'] == 1) {
                        $template = $this->settings['notification_order_received_message'];
                        $send_notification = true;
                    } elseif ($status == 3 || $this->settings['notification_order_processed_status'] == 1) {
                        $template = $this->settings['notification_order_processed_message'];
                        $send_notification = true;
                    } elseif ($status == 4 || $this->settings['notification_order_shipped_status'] == 1) {
                        $template = $this->settings['notification_order_shipped_message'];
                        $send_notification = true;
                    } elseif ($status == 5 || $this->settings['notification_order_out_for_delivery_status'] == 1) {
                        $template = $this->settings['notification_order_out_for_delivery_message'];
                        $send_notification = true;
                    } elseif ($status == 6 || $this->settings['notification_order_delivered_status'] == 1) {
                        $template = $this->settings['notification_order_delivered_message'];
                        $send_notification = true;
                    } elseif ($status == 7 || $this->settings['notification_order_cancelled_status'] == 1) {
                        $template = $this->settings['notification_order_cancelled_message'];
                        $send_notification = true;
                    }
                    if ($send_notification) {
                        $placeholders = [
                            '{userName}' => $userDetails['name'] ?? '',
                            '{orderId}' => $selectOrderDetails['order_id'] ?? '',
                        ];
                        $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);

                        sendFirebaseNotification($userToken['app_key'], 'Your order status updated to ' . $statusDetails['status'], $finalMessage, $dataForNotification);
                    }
                }
                $inserdata = [
                    'orders_id' => $order_id,
                    'status' => $status,
                    'created_by' => $session->get('user_id'),
                    'user_type' => 'Admin',
                    'created_at' => date("Y-m-d H:i:s")
                ];

                $ifAlredyStatusPresent = $OrderStatusesModel->where('orders_id', $order_id)->where('status', $status)->first();
                if(isset($ifAlredyStatusPresent['id'])){
                    $OrderStatusesModel->delete($ifAlredyStatusPresent['id']);
                }
                $OrderStatusesModel->insert($inserdata); 
                return $this->response->setJSON(['success' => true, 'message' => 'Order status updated successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
            }
        }
    }

    public function assignDeliveryBoy()
    {
        $session = session();
        helper('firebase_helper');

        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('manage-orders')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $order_id = $this->request->getPost('order_id');
            $delivery_boy_id = $this->request->getPost('delivery_boy_id');

            $orderModel = new OrderModel();

            $data = ['delivery_boy_id' => $delivery_boy_id];
            $updateOrder = $orderModel->set($data)->where('id', $order_id)->update();
            if ($updateOrder) {
                $userDetails = $orderModel->select('user_id, order_id')->where('id', $order_id)->first();
                $deviceTokenModel = new DeviceTokenModel();
                $dataForNotification = [
                    'screen' => 'Notification',
                ];
                if ($this->settings['notification_order_delivery_boy_assign_status'] == 1) {
                    $userToken = $deviceTokenModel->where('user_type', 2)->where('user_id', $userDetails['user_id'])->orderBy('id', 'desc')->first();

                    if (isset($userToken['app_key'])) {
                        $template = $this->settings['notification_order_delivery_boy_assign_message'];
                        $placeholders = [
                            '{userName}' => $user['name'] ?? '',
                            '{orderId}' => $userDetails['order_id'] ?? '',
                        ];
                        $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);
                        sendFirebaseNotification($userToken['app_key'], 'Delivery Boy assigned successfully', $finalMessage, $dataForNotification);
                    }
                }

                $deliveryUserToken = $deviceTokenModel->where('user_type', 3)->where('user_id', $delivery_boy_id)->orderBy('id', 'desc')->first();
                if (isset($deliveryUserToken['app_key'])) {
                    sendFirebaseNotification($deliveryUserToken['app_key'], 'New Order Assigned For You', 'For order id ' . $order_id, $dataForNotification);
                }
                return $this->response->setJSON(['success' => true, 'message' => 'Delivery Boy assigned  successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
            }
        }
    }
    public function updateDeliveryDate()
    {
        $session = session();
        helper('firebase_helper');

        if ($session->has('user_id') && session('account_type') == 'Admin') {
            if (!can_edit('manage-orders')) {
                $output = ['success' => false, "message" => "Permission not allowed"];
                return $this->response->setJSON($output);
            }
            $order_id = $this->request->getPost('order_id');
            $delivery_date = $this->request->getPost('delivery_date');
            $timeslot = $this->request->getPost('timeslot');

            $orderModel = new OrderModel();

            $data = ['delivery_date' => $delivery_date, 'timeslot' => $timeslot];
            $updateOrder = $orderModel->set($data)->where('id', $order_id)->update();
            if ($updateOrder) {
                $userDetails = $orderModel->select('user_id, order_id')->where('id', $order_id)->first();

                if ($this->settings['notification_order_update_delivery_date_status'] == 1) {
                    $deviceTokenModel = new DeviceTokenModel();
                    $userToken = $deviceTokenModel->where('user_type', 2)->where('user_id', $userDetails['user_id'])->orderBy('id', 'desc')->first();
                    $dataForNotification = [
                        'screen' => 'Notification',
                    ];
                    if (isset($userToken['app_key'])) {
                        $template = $this->settings['notification_order_update_delivery_date_message'];
                        $placeholders = [
                            '{userName}' => $user['name'] ?? '',
                            '{orderId}' => $userDetails['order_id'] ?? '',
                        ];
                        $finalMessage = str_replace(array_keys($placeholders), array_values($placeholders), $template);
                        sendFirebaseNotification($userToken['app_key'], 'Delivery date assigned successfully', $finalMessage, $dataForNotification);
                    }
                }
                return $this->response->setJSON(['success' => true, 'message' => 'Delivery date assigned successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Something went wrong']);
            }
        }
    }
}
