<?php

use App\Models\OrderModel;

if (!function_exists('totalOrderCount')) {
    function totalOrderCount()
    {
        $orderModel = new OrderModel();
        return $orderModel->getTotalOrders();
    }
}

if (!function_exists('orderCountStatusWise')) {
    function orderCountStatusWise($status)
    {
        $orderModel = new OrderModel();
        return $orderModel->getOrdersByStatus($status);
    }
}

if (!function_exists('totalOrderCountForSeller')) {
    function totalOrderCountForSeller()
    {
        $orderModel = new OrderModel();
        return $orderModel->getTotalOrdersForSeller();
    }
}

if (!function_exists('orderCountStatusWiseForSeller')) {
    function orderCountStatusWiseForSeller($status)
    {
        $orderModel = new OrderModel();
        return $orderModel->getOrdersByStatusForSeller($status);
    }
}
