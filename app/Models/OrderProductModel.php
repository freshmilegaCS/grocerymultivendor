<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderProductModel extends Model
{
    protected $table = 'order_products';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'seller_id', 'order_id', 'product_id', 'product_variant_id', 'product_name', 'product_variant_name', 'quantity', 'price', 'discounted_price', 'tax_amount', 'tax_percentage', 'discount'];
}
