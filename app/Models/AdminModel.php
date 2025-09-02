<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'fname', 'lname', 'username', 'mobile', 'password', 'token', 'reset_link_token', 'reset_token_exp_date'];
    

    public function getUserlist()
    {
        $db = \Config\Database::connect();

        // Select subcategory details along with category name and product count
        $builder = $db->table($this->table);
        $builder->select('admin.*, roles.name');
        $builder->join('roles', 'roles.id = admin.role_id');

        return $builder->get()->getResultArray();
    }

}
