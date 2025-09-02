<?php

namespace App\Models;

use CodeIgniter\Model;

class FaqsModel extends Model
{
    protected $table            = 'faqs';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['row_order', 'question', 'answer', 'status'];


    public function getAllFaqs()
    {
        return $this->select('id, question, answer')->orderBy('row_order', 'ASC')->where('status', 1)->findAll();
    }

    public function addFaq($data)
    {
        return $this->insert($data);
    }

    public function updateFaq($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteFaq($id)
    {
        return $this->delete($id);
    }
}
