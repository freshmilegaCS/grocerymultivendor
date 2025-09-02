<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryBoyTransactionModel extends Model
{
    protected $table      = 'delivery_boy_transaction';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = ['user_id', 'order_id', 'delivery_boy_id', 'type', 'amount', 'status', 'message', 'transaction_date', 'created_at', 'updated_at'];

    // Timestamps (since `created_at` and `updated_at` fields are present)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
