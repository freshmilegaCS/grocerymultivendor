<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryGroupModel extends Model
{
    protected $table      = 'category_group';     
    protected $primaryKey = 'id';                 
    protected $allowedFields = ['title', 'created_at'];  
    protected $useTimestamps = false;             
}
