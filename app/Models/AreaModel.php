<?php

namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{
    protected $table = 'area';           // Table name
    protected $primaryKey = 'id';        // Primary key of the table
    protected $useAutoIncrement = true;  // If your primary key is auto-increment
    protected $returnType = 'array';     // Return type of the query results
    protected $allowedFields = ['name', 'delivery_charge', 'is_active']; // Fields that can be manipulated

    public function getActiveAreas()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getAreaByName($areaName)
    {
        return $this->where(['name' => $areaName])
                    ->first();
    }
    
}
