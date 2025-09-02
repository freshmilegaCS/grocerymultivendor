<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturnRequestModel extends Model
{
    protected $table = 'return_requests'; // Table name
    protected $primaryKey = 'id';         // Primary key

    // Allowed fields for insert/update
    protected $allowedFields = [
        'order_item_id',
        'reason',
        'status',
        'remarks',
        'delivery_boy_id',
        'created_at',
        'updated_at'
    ];

}
