<?php

namespace App\Models;

use CodeIgniter\Model;


class RolePermissionModel extends Model
{
    protected $table      = 'roles_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'perm_cat_id', 'can_view', 'can_add', 'can_edit', 'can_delete', 'created_at'];

    public function insertRolePermissions($data)
    {
        return $this->insert($data);
    }
    public function getPermissionsByRole($roleId)
    {
        return $this->where('role_id', $roleId)->findAll();
    }
}
