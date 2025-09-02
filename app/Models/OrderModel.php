<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $allowedFields = ['order_id', 'user_id', 'address_id', 'payment_method_id', 'coupon_id', 'order_delivery_otp', 'delivery_date', 'timeslot', 'order_date', 'status', 'delivery_boy_id', 'transaction_id', 'delivery_sign', 'subtotal', 'tax', 'delivery_charge', 'used_wallet_amount', 'coupon_amount', 'additional_charge', 'created_at', 'updated_at', 'delivery_method', 'payment_json', 'note', 'order_delivery_otp_verification'];

    // Specify the return type as an array
    protected $returnType     = 'array';

    // Optionally define validation rules, messages, etc.
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getOrdersByDateRange($start_date, $end_date)
    {
        return $this->where('order_date >=', $start_date)
            ->where('order_date <=', $end_date)
            ->findAll();
    }

    public function getOrdersByStatusAndDate($status, $start_date, $end_date)
    {
        return $this->where('status', $status)
            ->where('order_date >=', $start_date)
            ->where('order_date <=', $end_date)
            ->findAll();
    }
    public function getOrderById(int $id)
    {
        return $this->where('id', $id)->first();
    }

    public function getProductById(int $productId)
    {
        return $this->db->table('product')
            ->where('id', $productId)
            ->get()
            ->getRowArray();
    }

    // Get orders by user id
    public function getOrdersByUserId($userId)
    {
        return $this->where('user_id', $userId)->orderBy('id', 'DESC')->findAll();
    }

    public function insertOrder($data)
    {
        $this->insert($data);
        return $this->insertID(); // Get the last inserted ID
    }

    public function getOrderDetailsById($orderId, $userId)
    {
        return $this->where('user_order_id', $orderId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();
    }

    public function getTotalOrders()
    {
        return $this->countAllResults();
    }
    public function getTotalOrdersForSeller()
    {
        return $this->join('order_products', 'order_products.order_id = orders.id', 'left')
            ->where('order_products.seller_id', session()->get('user_id'))
            ->countAllResults();
    }

    public function getOrdersByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }

    public function getOrdersByStatusForSeller($status)
    {
        return $this->selectCount('orders.id', 'total_orders')
            ->join('order_products', 'order_products.order_id = orders.id', 'left')
            ->where('orders.status', $status)
            ->where('order_products.seller_id', session()->get('user_id'))
            ->groupBy('order_products.seller_id')
            ->countAllResults();
    }

    public function getDailyOrderCountForCurrentMonth()
    {
        $currentMonth = date('Y-m');
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        // Generate all dates for the current month
        $period = new \DatePeriod(
            new \DateTime($startOfMonth),
            new \DateInterval('P1D'),
            new \DateTime($endOfMonth . ' +1 day')
        );

        $dates = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = [
                'day' => $date->format('d-M'),
                'order_count' => 0
            ];
        }

        // Fetch order counts
        $builder = $this->db->table($this->table);
        $builder->select('DATE(order_date) as date, COUNT(id) as order_count');
        $builder->where('DATE_FORMAT(order_date, "%Y-%m")', $currentMonth);
        $builder->groupBy('DATE(order_date)');
        $query = $builder->get();
        $orderCounts = $query->getResultArray();

        // Merge order counts with the complete date list
        foreach ($orderCounts as $orderCount) {
            $dates[$orderCount['date']]['order_count'] = $orderCount['order_count'];
        }

        return array_values($dates);
    }
    public function getMonthlyOrderCountForCurrentYear()
    {
        $currentYear = date('Y');

        // Fetch order counts
        $builder = $this->db->table($this->table);
        $builder->select('MONTH(order_date) as month, COUNT(id) as order_count');
        $builder->where('YEAR(order_date)', $currentYear);
        $builder->groupBy('MONTH(order_date)');
        $query = $builder->get();
        $orderCounts = $query->getResultArray();

        // Prepare month-wise data for the entire year
        $months = array_fill(1, 12, 0);
        foreach ($orderCounts as $orderCount) {
            $months[$orderCount['month']] = $orderCount['order_count'];
        }

        $data = [];
        foreach ($months as $month => $count) {
            $data[] = [
                'month' => date('F', mktime(0, 0, 0, $month, 10)), // Get month name
                'order_count' => $count
            ];
        }

        return $data;
    }

    public function getDailyOrderCountForCurrentMonthForSeller()
    {
        $currentMonth = date('Y-m');
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        // Generate all dates for the current month
        $period = new \DatePeriod(
            new \DateTime($startOfMonth),
            new \DateInterval('P1D'),
            new \DateTime($endOfMonth . ' +1 day')
        );

        $dates = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = [
                'day' => $date->format('d-M'),
                'order_count' => 0
            ];
        }

        // Fetch order counts
        $builder = $this->db->table($this->table);
        $builder->select('DATE(orders.order_date) as date, COUNT(DISTINCT orders.id) as order_count');
        $builder->join('order_products', 'order_products.order_id = orders.id', 'left');
        $builder->where('DATE_FORMAT(orders.order_date, "%Y-%m")', $currentMonth);
        $builder->where('order_products.seller_id', session()->get('user_id'));
        $builder->groupBy('DATE(orders.order_date)');
        $builder->groupBy('DATE(orders.id)');
        $query = $builder->get();
        $orderCounts = $query->getResultArray();
        // Merge order counts with the complete date list
        foreach ($orderCounts as $orderCount) {
            $dates[$orderCount['date']]['order_count'] = $orderCount['order_count'];
        }

        return array_values($dates);
    }
    public function getMonthlyOrderCountForCurrentYearForSeller()
    {
        $currentYear = date('Y');

        // Fetch order counts
        $builder = $this->db->table($this->table);
        $builder->select('MONTH(orders.order_date) as month, COUNT(DISTINCT orders.id) as order_count');
        $builder->join('order_products', 'order_products.order_id = orders.id', 'left');
        $builder->where('YEAR(orders.order_date)', $currentYear);
        $builder->where('order_products.seller_id', session()->get('user_id'));
        $builder->groupBy('MONTH(orders.order_date)');
        $builder->groupBy('DATE(orders.id)');
        $query = $builder->get();
        $orderCounts = $query->getResultArray();

        // Prepare month-wise data for the entire year
        $months = array_fill(1, 12, 0);
        foreach ($orderCounts as $orderCount) {
            $months[$orderCount['month']] = $orderCount['order_count'];
        }

        $data = [];
        foreach ($months as $month => $count) {
            $data[] = [
                'month' => date('F', mktime(0, 0, 0, $month, 10)), // Get month name
                'order_count' => $count
            ];
        }

        return $data;
    }

    public function assignDeliveryBoy($orderId, $deliveryBoyId)
    {
        return $this->update($orderId, ['delivery_boy_id' => $deliveryBoyId]);
    }

    public function getOrderDetails($orderId)
    {
        return $this->select('user_id, user_order_id')
            ->where('id', $orderId)
            ->first();
    }

    public function updateOrderWithSignature($order_id, $filePath)
    {
        return $this->where('id', $order_id)
            ->set(['delivery_sign' => $filePath, 'status' => 3])
            ->update();
    }

    public function getOrdersByDeliveryBoyId($deliveryBoyId)
    {
        return $this->where('delivery_boy_id', $deliveryBoyId)
            ->orderBy('status')
            ->findAll();
    }

    public function getTotalPendingOrders($deliveryBoyId)
    {
        return $this->where('delivery_boy_id', $deliveryBoyId)
            ->whereIn('status', [1, 2])
            ->countAllResults();
    }

    public function getTotalCompletedOrders($deliveryBoyId)
    {
        return $this->where('delivery_boy_id', $deliveryBoyId)
            ->where('status', 3)
            ->countAllResults();
    }

    public function getTodayPendingOrders($deliveryBoyId, $todayDate)
    {
        return $this->where('delivery_boy_id', $deliveryBoyId)
            ->whereIn('status', [1, 2])
            ->where('delivery_date', $todayDate)
            ->countAllResults();
    }

    public function getOrdersByDeliveryBoyAndDate($deliveryBoyId, $deliveryDate)
    {
        return $this->where([
            'delivery_boy_id' => $deliveryBoyId,
            'delivery_date'   => $deliveryDate
        ])->findAll();
    }

    public function updateOrderStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    public function getSalesByLocation()
    {
        return $this->db->table('orders')
            ->select('city.name AS city_name, SUM(orders.subtotal) AS total_sales')
            ->join('address', 'orders.address_id = address.id', 'left')
            ->join('city', 'address.city_id = city.id', 'left')
            ->groupBy('city.name')
            ->orderBy('total_sales', 'DESC')
            ->get()
            ->getResultArray();
    }
    public function getAverageOrderValue()
    {
        $result = $this->db->table($this->table)
            ->select('AVG(subtotal) AS avg_order_value')
            ->where('status', 6) // Only count completed orders
            ->get()
            ->getRowArray();

        return $result['avg_order_value'] ?? 0;
    }
}
 