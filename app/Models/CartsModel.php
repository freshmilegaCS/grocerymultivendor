<?php

namespace App\Models;

use CodeIgniter\Model;

class CartsModel extends Model
{
    protected $table = 'carts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['guest_id', 'user_id', 'product_id', 'product_variant_id', 'seller_id', 'quantity', 'save_for_later', 'created_at'];
}
