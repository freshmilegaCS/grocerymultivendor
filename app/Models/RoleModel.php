<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'slug', 'is_active', 'is_system', 'is_superadmin', 'created_at'] ;
    
    public function getActiveRoles()
    {
        return $this->where('is_active', 1)->findAll();
    }
    public function insertRole($data)
    {
        $this->insert($data);
        return $this->insertID();
    }
}
