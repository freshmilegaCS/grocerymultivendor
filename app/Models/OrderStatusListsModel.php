<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderStatusListsModel extends Model
{
    protected $table      = 'order_status_lists';
    protected $primaryKey = 'id';
    protected $allowedFields = ['status', 'color', 'text_color', 'bg_color', 'app_text_color', 'app_bg_color'];

}
