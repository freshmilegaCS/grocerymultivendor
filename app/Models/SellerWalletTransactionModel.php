<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerWalletTransactionModel extends Model
{
    protected $table = 'seller_wallet_transaction'; // Table name
    protected $primaryKey = 'id'; // Primary key

    // Allowed fields for insertion/updating
    protected $allowedFields = ['order_id', 'order_products_id', 'seller_id', 'type', 'amount', 'message', 'remark', 'status', 'created_at', 'updated_at', 'this_is_request', 'transaction_done_by'];
}
