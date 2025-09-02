<?php

namespace App\Models;

use CodeIgniter\Model;

class TagsModel extends Model
{
    protected $table      = 'tags';
    protected $primaryKey = 'id';

    // Fields that are allowed for insert/update
    protected $allowedFields = [
        'name', 'created_at', 'updated_at'
    ];

    // Enable timestamps for automatic handling of created_at and updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getTagByName($name) {
        return $this->where('name', $name)->first();
    }

    // Method to add a new tag
    public function addTag($tagName) {
        $data = [
            'name' => $tagName,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return  $this->insert($data);
    }
    
    // Method to fetch all tags for the Select2 AJAX request
    public function getAllTags($name) {
        return $this->like('name', $name)->findAll();
    }

    public function tagExists($tagName)
    {
        return $this->where('name', $tagName)->countAllResults() > 0;
    }
  

}
