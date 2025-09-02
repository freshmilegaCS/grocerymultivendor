<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionCategoryModel extends Model
{
    protected $table      = 'permission_category';
    protected $primaryKey = 'id';
    protected $allowedFields = ['row_order_by', 'name', 'short_code', 'enable_view', 'enable_add', 'enable_edit', 'enable_delete', 'created_at'];
    
    public function getPermissions()
    {
        return $this->findAll();
    }
    public function getAllCategories()
    {
        return $this->orderBy('row_order_by')->findAll();
    }
}