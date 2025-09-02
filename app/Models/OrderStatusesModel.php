<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderStatusesModel extends Model
{
    protected $table      = 'order_statuses';
    protected $primaryKey = 'id';
    protected $allowedFields = ['orders_id', 'order_products_id', 'status', 'created_by', 'user_type', 'created_at'];

}
