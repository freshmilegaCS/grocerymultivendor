<?php

namespace App\Models;

use CodeIgniter\Model;

class BannerModel extends Model
{
    protected $table = 'banner';
    protected $primaryKey = 'id';
    protected $allowedFields = ['banner_img', 'category_id', 'status'];

    // Function to get active banners and their categories
    public function getActiveBanners()
    {
        // Fetch banners with status 0
        $banners = $this->orderBy('id', 'DESC')
            ->findAll();

        // Load the category model for fetching category names
        $categoryModel = new CategoryModel();

        // Build the output array
        $output = [];
        $x = 1;
        foreach ($banners as $banner) {
            $categoryName = $categoryModel->getCategoryName($banner['category_id']);
            $output[] = [
                'number' => $x,
                'category' => $categoryName,
                'image' => $banner['banner_img'],
                'id' => $banner['id'],
                'status' => $banner['status']
            ];
            $x++;
        }

        return $output;
    }
    public function insertBanner($data)
    {
        return $this->insert($data);
    }
    public function deleteBanner($id)
    {
        return $this->delete($id);
    }
    
    public function getActiveBannerForApp($status)
    {
        $banners = $this->where('status', $status)->findAll();
        $subcategoryModel = new \App\Models\SubcategoryModel(); // Load Subcategory model

        foreach ($banners as &$banner) {
            if ($banner['category_id'] == 0) {
                $banner['firstSubcategory'] = []; // Set empty array if category_id is 0
            } else {
                $banner['firstSubcategory'] = $subcategoryModel
                    ->where('category_id', $banner['category_id'])
                    ->orderBy('row_order', 'ASC')
                    ->first() ?? []; // Set empty array if no subcategory is found
            }
        }

        return $banners;
    }

    public function getBannersByCategory($categoryId)
    {
        return $this->where('status', 4)
            ->where('category_id', $categoryId)
            ->findAll();
    }
}
