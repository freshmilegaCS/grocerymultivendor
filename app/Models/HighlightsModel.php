<?php

namespace App\Models;

use CodeIgniter\Model;

class HighlightsModel extends Model
{
    protected $table            = 'highlights';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['title', 'description', 'video', 'image', 'seller_id', 'created_at', 'is_active'];

    public function getAllHighlights()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }


    public function getHighlightById($id)
    {
        return $this->where('id', $id)->first();
    }


    public function addHighlight($data)
    {
        return $this->insert($data);
    }


    public function updateHighlight($id, $data)
    {
        return $this->update($id, $data);
    }


    public function deleteHighlight($id)
    {
        return $this->delete($id);
    }


    public function getActiveHighlights()
    {
        return $this->where('is_active', 1)->orderBy('created_at', 'DESC')->findAll();
    }
}
