<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryTrackingModel extends Model
{
    protected $table = 'delivery_tracking';
    protected $primaryKey = 'id';
    protected $allowedFields = ['delivery_id', 'order_id', 'latitude', 'longitude', 'heading', 'last_updated'];
}
