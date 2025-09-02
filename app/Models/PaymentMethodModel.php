<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentMethodModel extends Model
{
    protected $table = 'payment_method';
    protected $primaryKey = 'id';
    protected $allowedFields = ['img', 'title', 'description', 'api_key', 'secret_key', 'status', 'screen_name'];

    public function getAllPaymentMethods()
    {
        return $this->findAll();
    }
    public function getAllActivePaymentMethods()
    {
        return $this->where('status', 1)->findAll();
    }
    // Method to get the API key for a specific payment method ID
    public function getApiKey($id)
    {
        return $this->select('api_key')
            ->where('status', 1)
            ->where('id', $id)
            ->first();
    }

    public function getPaymentMethodById($paymentMethodId)
    {
        return $this->where('id', $paymentMethodId)
            ->first();
    }

    public function updateMethod($Id, $data)
    {
        return $this->where('id', $Id)
            ->set($data)
            ->update();
    }
}
