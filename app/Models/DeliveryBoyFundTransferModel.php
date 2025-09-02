<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryBoyFundTransferModel extends Model
{
    protected $table      = 'delivery_boy_fund_transfer';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = [ 
       'delivery_boy_id', 'order_id', 'type', 'opening_balance', 'closing_balance', 'amount', 'status', 'message', 'created_at', 'updated_at' ];

    // Timestamps (since `created_at` and `updated_at` fields are present)
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
