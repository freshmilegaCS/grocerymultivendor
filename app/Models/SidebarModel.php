<?php

namespace App\Models;

use CodeIgniter\Model;

class SidebarModel extends Model
{
    protected $table      = 'sidebar';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = ['row_order', 'title', 'url', 'for_account_type', 'icon', 'is_it_have_child', 'parent_id', 'is_it_header', 'permission_category_short_code', 'is_it_have_badge', 'badge_type', 'badge_function', 'badge_function_parameter'];

}
