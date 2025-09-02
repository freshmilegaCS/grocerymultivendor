<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductTagModel extends Model
{
    protected $table      = 'product_tag';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = ['product_id', 'tag_id', 'created_at', 'updated_at'];


   
}
