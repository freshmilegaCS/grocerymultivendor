<?php

namespace App\Models;

use CodeIgniter\Model;

class AddressModel extends Model
{
    protected $table = 'address';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'city_id', 'address', 'area', 'city', 'state', 'pincode', 'status', 'latitude', 'longitude', 'map_address', 'is_delete', 'deliverable_area_id', 'address_type', 'flat', 'floor', 'landmark', 'user_name', 'user_mobile'];

    
    public function deactivateAddresses($userId)
    {
        $this->where('user_id', $userId)->set(['status' => 0])->update();
    }

    public function deleteAddress($userId, $addressId)
    {
        return $this->where('user_id', $userId)
            ->where('id', $addressId)
            ->where('status', 0)
            ->set('is_delete', 1)
            ->update();
    }

    public function getLatestAddressByUserId($userId)
    {
        return $this->where(['user_id' => $userId, 'status' => 1, 'is_delete' => 0])
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function getAddressById($addressId)
    {
        return $this->where('id', $addressId)
            ->get()
            ->getRowArray();
    }

    public function getAddressDetails($addressId)
    {
        return $this->select('user_id, home_number, area, pincode, landmark, type, address_name, city')
            ->where('id', $addressId)
            ->get()
            ->getRowArray();
    }
}
