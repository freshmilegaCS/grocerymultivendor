<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductSortTypeModel extends Model
{
    protected $table = 'product_sort_type';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sort']; // Include the fields present in the table

    // Additional methods for specific queries can be added here if needed
}
