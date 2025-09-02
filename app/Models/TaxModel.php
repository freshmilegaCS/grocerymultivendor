<?php

namespace App\Models;

use CodeIgniter\Model;

class TaxModel extends Model
{
    protected $table = 'tax';
    protected $primaryKey = 'id';
    
    // Specify the fields that are allowed to be inserted/updated
    protected $allowedFields = ['tax', 'percentage', 'is_active', 'is_delete'];
    

}
