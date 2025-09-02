<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderReturnRequestModel extends Model
{
    protected $table = 'order_return_request'; // Table name
    protected $primaryKey = 'id';         // Primary key

    // Allowed fields for insert/update
    protected $allowedFields = ['order_id', 'order_products_id', 'reason', 'status', 'remark', 'delivery_boy_id', 'created_at', 'updated_at'];

}
