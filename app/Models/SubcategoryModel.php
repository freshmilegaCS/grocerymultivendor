<?php

namespace App\Models;

use CodeIgniter\Model;

class SubcategoryModel extends Model
{
    protected $table      = 'subcategory';
    protected $primaryKey = 'id';
    protected $allowedFields = ['category_id', 'row_order', 'name', 'slug', 'img'];

    public function getSubcategoriesWithDetails()
    {
        $db = \Config\Database::connect();

        // Select subcategory details along with category name and product count
        $builder = $db->table($this->table);
        $builder->select('subcategory.*, category.category_name, (SELECT COUNT(*) FROM product WHERE product.subcategory_id = subcategory.id) AS product_count');
        $builder->join('category', 'category.id = subcategory.category_id');
        $builder->orderBy('subcategory.id', 'DESC');

        return $builder->get()->getResultArray();
    }
    public function getSubcategoriesWithDetailsForSeller()
    {
        $db = \Config\Database::connect();

        // Select subcategory details along with category name and product count
        $builder = $db->table($this->table);
        $builder->select('subcategory.*, category.category_name, (SELECT COUNT(*) FROM product WHERE product.subcategory_id = subcategory.id) AS product_count');
        $builder->join('category', 'category.id = subcategory.category_id');
        $builder->orderBy('subcategory.id', 'DESC');
        $builder->join('seller_categories', 'seller_categories.category_id = category.id', 'left');
        $builder->where('seller_categories.seller_id', session()->get('user_id'));
        return $builder->get()->getResultArray();
    }
    public function insertSubcategory($data)
    {
        return $this->insert($data);
    }

    public function getSubcategoriesByCategoryId($categoryId)
    {
        return $this->where('category_id', $categoryId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }
    public function getSubcategoriesForFetchAllSubCategoryProductListByCategoryId($categoryId, $subcategoryId = 0)
    {
        if ($subcategoryId == 0) {
            return $this->where('category_id', $categoryId)->findAll();
        } else {
            return $this->where('id', $subcategoryId)->findAll();
        }
    }

    // Fetch subcategory details by ID
    public function getSubcategoryNameById($id)
    {
        return $this->select('name')->where('id', $id)->first();
    }
    public function getTotalSubcategories()
    {
        return $this->countAllResults();
    }

    public function getTotalSubcategoriesForSeller()
    {

        $db = \Config\Database::connect();

        // Select subcategory details along with category name and product count
        $builder = $db->table($this->table);
        $builder->join('category', 'category.id = subcategory.category_id');
        $builder->orderBy('subcategory.id', 'DESC');
        $builder->join('seller_categories', 'seller_categories.category_id = category.id', 'left');
        $builder->where('seller_categories.seller_id', session()->get('user_id'));
        return $builder->countAllResults();
    }
}
