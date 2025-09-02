<?php

namespace App\Models;

use CodeIgniter\Model;

class HeaderCategoryModel extends Model
{
    protected $table      = 'header_category';     
    protected $primaryKey = 'id';                 
    protected $allowedFields = ['title', 'icon', 'category_id', 'icon_library'];  
}
