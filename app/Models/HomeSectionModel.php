<?php

namespace App\Models;

use CodeIgniter\Model;

class HomeSectionModel extends Model
{
    protected $table = 'home';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'category_id', 'subcategory_id', 'is_active'];

    // Fetch all homes with associated category and subcategory names
    public function getAllHomeData()
    {
        $builder = $this->db->table($this->table);
        $builder->select('home.*, category.category_name, subcategory.name as subcategory_name')
                ->join('category', 'category.id = home.category_id', 'left')
                ->join('subcategory', 'subcategory.id = home.subcategory_id', 'left')
                ->orderBy('home.id', 'DESC');
        return $builder->get()->getResultArray();
    }

     // Insert a new home record
     public function insertHome($data)
     {
         return $this->insert($data);
     }

     public function getActiveHomeSections()
    {
        return $this->where('is_active', 1)->findAll();
    }
}
