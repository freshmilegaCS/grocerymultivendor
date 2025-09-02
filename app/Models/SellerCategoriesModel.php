<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerCategoriesModel extends Model
{
    protected $table      = 'seller_categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['seller_id', 'category_id']; 

    protected $useTimestamps = false;

    public function findIsCategoryActiveForAdmin($category_id, $seller_id)
    {
        return $this->select('id')
            ->where('seller_id', $seller_id)
            ->where('category_id', $category_id)
            ->first();
    }
    
    public function findIsCategoryActiveForSeller($category_id)
    {
        return $this->select('id')
            ->where('seller_id', session()->get('user_id'))
            ->where('category_id', $category_id)
            ->first();
    }

    public function findIsCategoryActiveForSellerAdminAPI($sellerid, $category_id)
    {
        return $this->select('id')
            ->where('seller_id', $sellerid)
            ->where('category_id', $category_id)
            ->first();
    }

    public function getCategoryBySeller($sellerid)
    {
        return $this->select('seller_categories.id, category.id as category_id, category.category_name')
            ->where('seller_categories.seller_id', $sellerid)
            ->join('category', 'seller_categories.category_id = category.id', 'left')
            ->findAll();
    }
}
