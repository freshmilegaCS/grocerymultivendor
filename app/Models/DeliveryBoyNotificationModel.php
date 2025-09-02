<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryBoyNotificationModel extends Model
{
    protected $table = 'delivery_boy_notification';
    protected $primaryKey = 'id';
    protected $allowedFields = ['delivery_boy_id', 'title', 'description', 'created_at'];

    public function getNotificationsByDeliveryBoyId($deliveryBoyId)
    {
        return $this->where('delivery_boy_id', $deliveryBoyId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
