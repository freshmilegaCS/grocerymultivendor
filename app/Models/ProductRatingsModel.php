<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductRatingsModel extends Model
{
    protected $table      = 'product_ratings';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = ['product_id', 'user_id', 'order_id', 'rate', 'title', 'review', 'created_at', 'is_approved_to_show', 'is_active', 'is_delete'];
}
