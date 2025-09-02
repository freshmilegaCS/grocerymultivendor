<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantsModel extends Model
{
    protected $table      = 'product_variants';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = ['product_id', 'status', 'title', 'price', 'discounted_price', 'stock', 'is_unlimited_stock', 'stock_unit_id', 'is_delete'];

}
