<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryBoyModel extends Model
{
    protected $table = 'delivery_boy';
    protected $primaryKey = 'id';
    protected $allowedFields = ['admin_id', 'city_id', 'name', 'mobile', 'password', 'balance', 'cash_collection_amount', 'address', 'bonus_type', 'bonus_percentage', 'bonus_min_amount', 'bonus_max_amount', 'driving_license', 'national_identity_card', 'dob', 'bank_account_number', 'bank_name', 'account_name', 'ifsc_code', 'other_payment_information', 'status', 'is_available', 'app_key', 'pincode', 'cash_received', 'created_at', 'updated_at', 'remark', 'is_delete', 'a_status'];

    // Table configuration
    protected $useTimestamps = true; // Enable timestamps
    protected $createdField = 'created_at'; // Field for created timestamp
    protected $updatedField = 'updated_at'; // Field for updated timestamp

    // Validation rules (optional, customize as needed)
    protected $validationRules = [
        'name' => 'required|max_length[191]',
        'mobile' => 'required|max_length[191]',
        'address' => 'required'
    ];

    public function getDeliveryBoyDetails($delivery_boy_id)
    {
        return $this->select('name, mobile')
            ->where('id', $delivery_boy_id)
            ->first();
    }
    public function getActiveDeliveryBoys()
    {
        return $this->select('delivery_boy.*, city.name as city_name')
                ->join('city', 'city.id = delivery_boy.city_id', 'left')
                ->where('delivery_boy.is_delete', 0)
                ->where('delivery_boy.a_status', 1)
                ->findAll();
    }
    public function getAllDeliveryBoys()
    {
        return $this->select('delivery_boy.*, city.name as city_name')
                ->join('city', 'city.id = delivery_boy.city_id', 'left')
                ->where('delivery_boy.is_delete', 0)
                ->findAll();
    }

    public function getDeliveryBoys()
    {
        return $this->where('is_delete', 0)
            ->findAll();
    }
    public function addDeliveryBoy($data)
    {
        return $this->insert($data);
    }
    public function getDeliveryBoyById($id)
    {
        return $this->where('id', $id)->first();
    }
    public function softDeleteDeliveryBoy($id)
    {
        return $this->update($id, ['is_delete' => 1]);
    }

    public function updateDetails($id, $data)
    {
        return $this->update($id, $data);
    }

    public function updateByMobile($mobile, $updateData)
    {
        return $this->where('mobile', $mobile)
            ->where('is_delete', 0)
            ->set($updateData)
            ->update();
    }
    public function getDeliveryBoy($mobile, $password)
    {
        return $this->where([
            'mobile'   => $mobile,
            'password' => $password,
            'is_delete' => 0
        ])->first();
    }

    public function updateToken($id, $token, $fcmToken)
    {
        return $this->update($id, [
            'token'   => $token,
            'app_key' => $fcmToken
        ]);
    }

    public function getActiveStatus($mobile)
    {
        return $this->where([
            'mobile'    => $mobile,
            'status'    => 1,
            'is_delete' => 0
        ])->select('a_status as active_status')->first();
    }

    public function getDeliveryBoyByMobile($mobile)
    {
        return $this->where([
            'mobile'    => $mobile,
            'status'    => 1,
            'is_delete' => 0
        ])->first();
    }

    public function updateStatusByMobile($mobile, $a_status)
    {
        // Check if the delivery boy exists with the given mobile, status, and is_delete conditions
        return $this->where('mobile', $mobile)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->set(['a_status' => $a_status])
            ->update();
    }

    public function updateImagePathByMobile($mobile, $imagePath)
    {
        return $this->where('mobile', $mobile)
            ->set(['imgpath' => $imagePath])
            ->update();
    }
    public function getDeliveryBoyAppKey($user_id)
    {
        return $this->select('app_key')
            ->where('id', $user_id)
            ->first();
    }
}
