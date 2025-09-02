<?php

namespace App\Models;

use CodeIgniter\Model;

class TimeZoneModel extends Model
{
    protected $table = 'timezone';
    protected $primaryKey = 'id';
    protected $allowedFields = ['timezone', 'gmt', 'is_active'];   
    
    public function setActiveTimeZone($timezone_id){
        $this->where('is_active', 1)->set('is_active', 0)->update();
        return $this->where('id', $timezone_id)->set('is_active', 1)->update();
    }
}
