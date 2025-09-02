<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceTokenModel extends Model
{
    protected $table = 'device_token'; // Name of the table
    protected $primaryKey = 'id'; // Primary key of the table

    // Fields that can be inserted or updated
    protected $allowedFields = ['user_type', 'user_id', 'app_key'];

    // Use timestamps if your table has `created_at` and `updated_at` columns
    protected $useTimestamps = false;

    // Fetch all records or apply specific filtering
    public function getDeviceTokens($filters = [])
    {
        if (!empty($filters)) {
            $this->where($filters);
        }
        return $this->select('app_key')->orderBy('id', 'desc')->first();
    }

    // Fetch a single record by ID
    public function getDeviceTokenById($id)
    {
        return $this->find($id);
    }
}
