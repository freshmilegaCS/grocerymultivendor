<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table      = 'brand';
    protected $primaryKey = 'id';
    protected $allowedFields = ['brand', 'slug', 'image', 'row_order'];

    public function getBrandList(){
        return $this->orderBy('row_order', 'ASC')->findAll();
    }
}
