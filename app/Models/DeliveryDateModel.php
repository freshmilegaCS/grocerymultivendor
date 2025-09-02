<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryDateModel extends Model
{
    protected $table = 'delivery_date';
    protected $primaryKey = 'id';
    protected $allowedFields = ['deliverable_area_id', 'date', 'created_at'];

}
