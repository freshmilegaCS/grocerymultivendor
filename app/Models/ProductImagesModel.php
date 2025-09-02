<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImagesModel extends Model
{
    protected $table      = 'product_images';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = [
        'product_id', 'product_variant_id', 'image'
    ];

}
