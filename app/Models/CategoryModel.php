<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table      = 'category';
    protected $primaryKey = 'id';
    protected $allowedFields = ['category_group_id','row_order', 'category_name', 'slug', 'category_img', 'is_bestseller_category', 'is_it_have_warning', 'warning_content', 'warning_link'];

    public function getCategoriesWithSubCount()
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $builder->select('category.*, (SELECT COUNT(*) FROM subcategory WHERE subcategory.category_id = category.id) AS sub_count');
        $builder->orderBy('category.id', 'DESC');
        $query = $builder->get();

        return $query->getResultArray();
    }
    public function getCategoriesForSellerWithSubCount()
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $builder->select('category.*, (SELECT COUNT(*) FROM subcategory WHERE subcategory.category_id = category.id) AS sub_count');
        $builder->orderBy('category.id', 'DESC');
        $builder->join('seller_categories', 'seller_categories.category_id = category.id', 'left');
        $builder->orderBy('category.row_order', 'ASC');
        $builder->where('seller_categories.seller_id', session()->get('user_id'));
        $query = $builder->get();

        return $query->getResultArray();
    }
    public function getCategories()
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }
    public function getCategoryName($categoryId)
    {
        if ($categoryId == 0) {
            return 'No Category Selected';
        }

        $category = $this->find($categoryId);
        return $category ? $category['category_name'] : 'Unknown Category';
    }
    public function getCategoryById($categoryId)
    {
        return $this->where('id', $categoryId)
            ->first();
    }
    public function getAllCategories()
    {
        return $this->findAll();
    }

    // Fetch category details by ID
    public function getCategoryNameById($id)
    {
        return $this->select('category_name')->where('id', $id)->first();
    }
    public function getTotalCategories()
    {
        return $this->countAllResults();
    }

    public function getTotalCategoriesForSeller()
    {
        return $this->join('seller_categories', 'seller_categories.category_id = category.id', 'left')
            ->orderBy('category.row_order', 'ASC')
            ->where('seller_categories.seller_id', session()->get('user_id'))
            ->countAllResults();
    }


    public function getAllCategoriesOrderWise()
    {
        return $this->orderBy('row_order', 'ASC')->findAll();
    }

    public function getCategoriesForSeller()
    {
        return $this->select('category.category_name, category.id')
            ->join('seller_categories', 'seller_categories.category_id = category.id', 'left')
            ->orderBy('category.row_order', 'ASC')
            ->where('seller_categories.seller_id', session()->get('user_id'))
            ->findAll();
    }
}
