<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use App\Models\OrderProductModel;
use App\Models\SellerModel;
use App\Models\SettingsModel;



class Report extends BaseController
{
    public function productSellingReport()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();

            return view('sellerPanel/report/productSellingReport', $data);
        } else {
            return redirect()->to('seller/auth/login');
        }
    }

    public function productSellingReportlist()
    {
        if (!session()->has('user_id') || session('account_type') !== 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }


        $orderProductModel = new OrderProductModel();
        $orderDate = $this->request->getPost('report_date');

        // Handle default order date
        if (empty($orderDate)) {
            $today = date('Y-m-d');
            $orderDate = "$today - $today";
        }
        $dates = explode(' - ', $orderDate);

        // Base query
        $builder = $orderProductModel->select(
            'product.product_name,
        product_variants.title AS variant_name,
        SUM(order_products.quantity) AS total_product_sold,
        SUM(order_products.price * order_products.quantity) AS total_amount'
        );
        $builder->join('product_variants', 'order_products.product_variant_id = product_variants.id', 'left');
        $builder->join('product', 'order_products.product_id = product.id', 'left');
        $builder->join('orders', 'order_products.order_id = orders.id', 'left');
        $builder->where('order_products.seller_id', session()->get('user_id'));
        $builder->where('orders.status', 6);
        $builder->groupBy('order_products.product_variant_id');

        if (!empty($dates) && is_array($dates)) {
            $builder->where('DATE(orders.order_date) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(orders.order_date) <=', date('Y-m-d', strtotime($dates[1])));
        }



        // Fetch data
        $query = $builder->get();
        $reports = $query->getResultArray();

        // Prepare output
        $output['data'] = [];
        foreach ($reports as  $order) {

            $output['data'][] = [
                $order['product_name'],
                $order['variant_name'],
                $order['total_product_sold'],
                $order['total_amount'],
            ];
        }

        return $this->response->setJSON($output);
    }
    public function sellingReport()
    {
        $session = session();
        if ($session->has('user_id') && session('account_type') == 'Seller') {

            $settingModel = new SettingsModel();
            $data['settings'] = $settingModel->getSettings();
            $sellerModel = new SellerModel();
            $data['sellerInfo'] = $sellerModel->select('view_customer_details')->where('id', session()->get('user_id'))->where('is_delete', 0)->where('status', 1)->first();
            return view('sellerPanel/report/sellingReport', $data);
        } else {
            return redirect()->to('seller/auth/login');
        }
    }
    public function sellingReportlist()
    {
        if (!session()->has('user_id') || session('account_type') !== 'Seller') {
            return redirect()->to('admin/login'); // Redirect to login if session is not set
        }


        $orderProductModel = new OrderProductModel();
        $orderDate = $this->request->getPost('report_date');

        // Handle default order date
        if (empty($orderDate)) {
            $today = date('Y-m-d');
            $orderDate = "$today - $today";
        }
        $dates = explode(' - ', $orderDate);

        // Base query
        $builder = $orderProductModel->select(
            ' orders.id AS order_id, order_products.id AS order_item_id,
        user.name AS user_name,
        product.product_name,
        product_variants.title AS variant_name,
        (order_products.quantity * order_products.price) AS total,
        orders.created_at AS date'
        );
        $builder->join('orders', 'order_products.order_id = orders.id', 'left');
        $builder->join('product', 'order_products.product_id = product.id', 'left');
        $builder->join('user', 'orders.user_id = user.id', 'left');
        $builder->join('product_variants', 'order_products.product_variant_id = product_variants.id', 'left');
        $builder->orderBy('orders.created_at', 'DESC'); // Sort by most recent orders
        $builder->where('order_products.seller_id', session()->get('user_id'));

        // $builder->where('orders.status', 6);

        if (!empty($dates) && is_array($dates)) {
            $builder->where('DATE(orders.order_date) >=', date('Y-m-d', strtotime($dates[0])));
            $builder->where('DATE(orders.order_date) <=', date('Y-m-d', strtotime($dates[1])));
        }



        // Fetch data
        $query = $builder->get();
        $reports = $query->getResultArray();

        // Prepare output
        $output['data'] = [];
        $sellerModel = new SellerModel();
        $sellerInfo = $sellerModel->select('view_customer_details')->where('id', session()->get('user_id'))->where('is_delete', 0)->where('status', 1)->first();

        foreach ($reports as $index => $order) {

            $order_id = "<a data-tooltip='tooltip' target='_blank' title='View Order' href='" . base_url("seller/orders/view/{$order['order_id']}") . "'>
                        ".$order['order_id']."</i></a>";
            if ($sellerInfo['view_customer_details'] == 1) {
                $output['data'][] = [
                    $order_id,
                    $order['order_item_id'],
                    $order['user_name'],
                    $order['product_name'],
                    $order['variant_name'],
                    $order['total'],
                    $order['date'],
                ];
            } {
                $output['data'][] = [
                    $order_id,
                    $order['order_item_id'],
                    $order['product_name'],
                    $order['variant_name'],
                    $order['total'],
                    $order['date'],
                ];
            }
        }

        return $this->response->setJSON($output);
    }
}
