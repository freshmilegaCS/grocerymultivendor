<?php

namespace App\Models;

use CodeIgniter\Model;

class SmsGatewayModel extends Model
{
    protected $table      = 'sms_gateway';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'value', 'is_active'];

    public function getAllSMSGateway()
    {
        return $this->findAll();
    }
}