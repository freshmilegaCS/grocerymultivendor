<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table      = 'product';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_name', 'brand_id', 'seller_id', 'category_id', 'subcategory_id', 'row_order', 'description', 'status', 'main_img', 'date', 'popular', 'deal_of_the_day', 'is_delete', 'slug', 'tax_id', 'manufacturer', 'made_in', 'is_returnable', 'return_days', 'total_allowed_quantity', 'tax_included_in_price', 'fssai_lic_no', 'seo_title', 'seo_keywords', 'seo_alt_text', 'seo_description', 'added_by_seller'];

    public function insertProduct($data)
    {
        return $this->insert($data);
    }

    // Function to get products with category and subcategory details
    public function getProductsList($category, $seller,  $status, $stock)
    {
        $db = \Config\Database::connect();

        $builder = $db->table($this->table);
        $builder->select('product.*, category.category_name, subcategory.name as subcategory_name, seller.store_name, brand.brand');
        $builder->join('category', 'category.id = product.category_id', 'left');
        $builder->join('seller', 'seller.id = product.seller_id', 'left');
        $builder->join('brand', 'brand.id = product.brand_id', 'left');
        $builder->join('subcategory', 'subcategory.id = product.subcategory_id', 'left');
        $builder->where('product.is_delete', 0);

        if (!empty($seller)) {
            $builder->where('product.seller_id', $seller);
        }
        if (!empty($category)) {
            $builder->where('product.category_id', $category);
        }
        if ($status == 0 || $status == 1) {
            $builder->where('product.status', $status);
        }
        $builder->orderBy('product.id', 'DESC');

        $products = $builder->get()->getResultArray();
        // Fetch and add variations for each product
        $productVariantsModel = new ProductVariantsModel();
        foreach ($products as &$product) {
            if ($stock == 1) {
                // Fetch products with stock > 0 or unlimited stock
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->groupStart()
                    ->where('stock >', 0)
                    ->orWhere('is_unlimited_stock', 1)
                    ->groupEnd()
                    ->findAll();
            } elseif ($stock === "0") {
                // Fetch products with stock equal to 0
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->groupStart()
                    ->where('stock', 0)
                    ->Where('is_unlimited_stock', 0)
                    ->groupEnd()
                    ->findAll();
            } elseif ($stock == 2) {
                // Fetch all active variants without stock filtering
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->groupStart()
                    ->where('stock <', 50)
                    ->Where('is_unlimited_stock', 0)
                    ->groupEnd()
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->orderBy('stock', 'desc')
                    ->findAll();
            } else {
                // Fetch all active variants without stock filtering
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->findAll();
            }
        }

        return $products;
    }
    public function getRequestProductsList($category, $seller,  $status, $stock)
    {
        $db = \Config\Database::connect();

        $builder = $db->table($this->table);
        $builder->select('product.*, category.category_name, subcategory.name as subcategory_name, seller.store_name, brand.brand');
        $builder->join('category', 'category.id = product.category_id', 'left');
        $builder->join('seller', 'seller.id = product.seller_id', 'left');
        $builder->join('brand', 'brand.id = product.brand_id', 'left');
        $builder->join('subcategory', 'subcategory.id = product.subcategory_id', 'left');
        $builder->where('product.is_delete', 0);
        $builder->where('product.added_by_seller', 1);

        if ($seller) {
            $builder->where('product.seller_id', $seller);
        }
        if ($category) {
            $builder->where('product.category_id', $category);
        }
        if ($status == 0 || $status == 1) {
            $builder->where('product.status', $status);
        }
        $builder->orderBy('product.id', 'DESC');

        $products = $builder->get()->getResultArray();
        // Fetch and add variations for each product
        $productVariantsModel = new ProductVariantsModel();
        foreach ($products as &$product) {
            if ($stock == 1) {
                // Fetch products with stock > 0 or unlimited stock
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->groupStart()
                    ->where('stock >', 0)
                    ->orWhere('is_unlimited_stock', 1)
                    ->groupEnd()
                    ->findAll();
            } elseif ($stock === "0") {
                // Fetch products with stock equal to 0
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->groupStart()
                    ->where('stock', 0)
                    ->Where('is_unlimited_stock', 0)
                    ->groupEnd()
                    ->findAll();
            } elseif ($stock == 2) {
                // Fetch all active variants without stock filtering
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->groupStart()
                    ->where('stock <', 50)
                    ->Where('is_unlimited_stock', 0)
                    ->groupEnd()
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->orderBy('stock', 'desc')
                    ->findAll();
            } else {
                // Fetch all active variants without stock filtering
                $product['variants'] = $productVariantsModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0) // Only non-deleted variants
                    ->findAll();
            }
        }

        return $products;
    }

    public function getProductsWithDetails()
    {
        $db = \Config\Database::connect();

        $builder = $db->table($this->table);
        $builder->select('product.*, category.category_name, subcategory.name as subcategory_name, seller.store_name, brand.brand');
        $builder->join('category', 'category.id = product.category_id', 'left');
        $builder->join('seller', 'seller.id = product.seller_id', 'left');
        $builder->join('brand', 'brand.id = product.brand_id', 'left');
        $builder->join('subcategory', 'subcategory.id = product.subcategory_id', 'left');
        $builder->where('product.is_delete', 0);
        $builder->orderBy('product.id', 'ASC');

        $products = $builder->get()->getResultArray();
        // Fetch and add variations for each product
        $productVariantsModel = new ProductVariantsModel();
        foreach ($products as &$product) {
            $product['variants'] = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)  // Fetch only active variants
                ->findAll();
        }

        return $products;
    }
    public function getProductsWithDetailsForSeller($seller_id)
    {
        $db = \Config\Database::connect();

        $builder = $db->table($this->table);
        $builder->select('product.*, category.category_name, subcategory.name as subcategory_name, seller.store_name, brand.brand');
        $builder->join('category', 'category.id = product.category_id', 'left');
        $builder->join('seller', 'seller.id = product.seller_id', 'left');
        $builder->join('brand', 'brand.id = product.brand_id', 'left');
        $builder->join('subcategory', 'subcategory.id = product.subcategory_id', 'left');
        $builder->where('product.is_delete', 0);
        $builder->where('product.seller_id', $seller_id);
        $builder->orderBy('product.id', 'ASC');

        $products = $builder->get()->getResultArray();
        // Fetch and add variations for each product
        $productVariantsModel = new ProductVariantsModel();
        foreach ($products as &$product) {
            $product['variants'] = $productVariantsModel
                ->where('product_id', $product['id'])
                ->where('is_delete', 0)  // Fetch only active variants
                ->findAll();
        }

        return $products;
    }
    public function getProductByCategoryId($categoryId)
    {
        return $this->where('product.category_id', $categoryId)
            ->orderBy('product.row_order')
            ->join('product_variants', 'product_variants.product_id = product.id', 'left')
            ->where('product.is_delete', 0)
            ->where('product_variants.is_delete', 0)
            ->where('product.seller_id !=', 0)
            ->where('product.slug IS NOT NULL AND product.slug !=', '')
            ->find();
    }
    public function isProductAvailable($productId, $variation, $price)
    {
        $result = $this->where([
            'id' => $productId,
            'variation LIKE' => '%' . $variation . '%',
            'price LIKE' => '%' . $price . '%',
            'stock' => 1,
            'status' => 1,
            'is_delete' => 0,
        ])->countAllResults();

        return $result === 1;
    }
    public function getPopularProducts($page, $rowPerPage = 6)
    {
        $start = ($page * $rowPerPage) - $rowPerPage;
        return $this->where('status', 1)
            ->where('popular', 1)
            ->where('deal_of_the_day', 0)
            ->where('is_delete', 0)
            ->findAll($rowPerPage, $start);
    }
    public function getProductsForFetchAllSubCategoryProductListByCategoryId($subcategoryId)
    {
        return $this->where('subcategory_id', $subcategoryId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->orderBy('id', 'desc')
            ->findAll();
    }
    public function getDealOfTheDayProducts($limit, $offset)
    {
        return $this->where([
            'status' => 1,
            'deal_of_the_day' => 1,
            'popular' => 0,
            'is_delete' => 0
        ])
            ->limit($limit, $offset)
            ->findAll();
    }

    // Method to fetch products by subcategory and category
    public function getProductsByCategory($subcategoryId, $categoryId, $limit = 6)
    {
        return $this->where('status', 1)
            ->where('subcategory_id', $subcategoryId)
            ->where('category_id', $categoryId)
            ->where('is_delete', 0)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    // Method to get products with pagination and filtering conditions
    public function getOfferProducts($page, $rowPerPage)
    {
        $begin = ($page * $rowPerPage) - $rowPerPage;

        return $this->where('status', 1)
            ->where('is_delete', 0)
            ->groupStart()
            ->where('discount >', 0)
            ->orWhere('deal_of_the_day', 1)
            ->groupEnd()
            ->orderBy('id', 'desc')
            ->findAll($rowPerPage, $begin);
    }

    // Fetch product details by ID
    public function getProductById($id)
    {
        return $this->where('id', $id)->where('is_delete', 0)->first();
    }
    public function searchProductsByName($keyword)
    {
        return $this->where('status', 1)
            ->where('is_delete', 0)
            ->like('product_name', $keyword)
            ->findAll();
    }
    public function getTotalProducts()
    {
        return $this->join('product_variants', 'product_variants.product_id = product.id', 'left')->where('product.is_delete', 0)->where('product_variants.is_delete', 0)->countAllResults();
    }

    public function getTotalProductsForSeller()
{
    return $this->select('COUNT(DISTINCT product.id) as total_products')
        ->join('product_variants', 'product_variants.product_id = product.id', 'left')
        ->where('product.is_delete', 0)
        ->where('product_variants.is_delete', 0)
        ->where('product.seller_id', session()->get('user_id'))
        ->get()
        ->getRowArray()['total_products'];
}


    //used in home contrller
    public function getAllPopularProduct()
    {
        $sellerModel = new SellerModel();
        $productVariantModel = new ProductVariantsModel();
        $cartsModel = new CartsModel();
        $products = [];

        // Check if session is set and get user details
        $user = null;
        if (session()->has('email')) {
            $userModel = new UserModel();
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        // Get sellers based on city ID
        $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();

        foreach ($sellers as $seller) {
            // Get all popular products from each seller
            $sellerProducts = $this->where('popular', 1)
                ->where('is_delete', 0)
                ->where('seller_id', $seller['id'])
                ->findAll();

            foreach ($sellers as $seller) {
                // Get all popular products from each seller
                $sellerProducts = $this->where('popular', 1)
                    ->where('is_delete', 0)
                    ->where('seller_id', $seller['id'])
                    ->findAll();

                foreach ($sellerProducts as $product) {
                    // 2) Fetch non-deleted variants for this product
                    $variants = $productVariantModel
                        ->where('product_id', $product['id'])
                        ->where('is_delete', 0)
                        ->findAll();

                    // 3) Only proceed if we actually got at least one variant
                    if (empty($variants)) {
                        // no variants → skip this product
                        continue;
                    }

                    // 4) Attach the variants array
                    $product['variants'] = $variants;

                    // 5) Default cart_quantity to zero
                    $product['cart_quantity'] = 0;

                    // 6) If the user is logged in, see if they have this in their cart
                    if (isset($user['id'])) {
                        $cartItem = $cartsModel
                            ->where('user_id', $user['id'])
                            ->where('product_id', $product['id'])
                            ->first();
                        if ($cartItem) {
                            $product['cart_quantity'] = (int) $cartItem['quantity'];
                        }
                    }

                    // 7) Finally, push into your output array
                    $products[] = $product;
                }
            }
        }

        $data['products'] = $products;
        return $data['products'];
    }

    public function getAllDealOfTheDayProduct()
    {
        $sellerModel = new SellerModel();
        $productVariantModel = new ProductVariantsModel();
        $cartsModel = new CartsModel();
        $products = [];

        // Check if session is set and get user details
        $user = null;
        if (session()->has('email')) {
            $userModel = new UserModel();
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        // Get sellers based on city ID
        $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();


            foreach ($sellers as $seller) {
                // Get all popular products from each seller
                $sellerProducts = $this->where('deal_of_the_day', 1)
                    ->where('is_delete', 0)
                    ->where('seller_id', $seller['id'])
                    ->findAll();

                foreach ($sellerProducts as $product) {
                    // 2) Fetch non-deleted variants for this product
                    $variants = $productVariantModel
                        ->where('product_id', $product['id'])
                        ->where('is_delete', 0)
                        ->findAll();

                    // 3) Only proceed if we actually got at least one variant
                    if (empty($variants)) {
                        // no variants → skip this product
                        continue;
                    }

                    // 4) Attach the variants array
                    $product['variants'] = $variants;

                    // 5) Default cart_quantity to zero
                    $product['cart_quantity'] = 0;

                    // 6) If the user is logged in, see if they have this in their cart
                    if (isset($user['id'])) {
                        $cartItem = $cartsModel
                            ->where('user_id', $user['id'])
                            ->where('product_id', $product['id'])
                            ->first();
                        if ($cartItem) {
                            $product['cart_quantity'] = (int) $cartItem['quantity'];
                        }
                    }

                    // 7) Finally, push into your output array
                    $products[] = $product;
                }
            }
        

        $data['products'] = $products;
        return $data['products'];
    }

    public function similarProducts($subcategory_id)
    {
        $sellerModel = new SellerModel();
        $productVariantModel = new ProductVariantsModel();
        $cartsModel = new CartsModel();
        $products = [];

        // Check if session is set and get user details
        $user = null;
        if (session()->has('email')) {
            $userModel = new UserModel();
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        // Get sellers based on city ID
        $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();

        foreach ($sellers as $seller) {
            // Get all popular products from each seller
            $sellerProducts = $this->where('subcategory_id', $subcategory_id)
                ->where('is_delete', 0)
                ->where('seller_id', $seller['id'])
                ->findAll();

            foreach ($sellers as $seller) {
                // Get all popular products from each seller
                $sellerProducts = $this->where('popular', 1)
                    ->where('is_delete', 0)
                    ->where('seller_id', $seller['id'])
                    ->findAll();

                foreach ($sellerProducts as $product) {
                    // 2) Fetch non-deleted variants for this product
                    $variants = $productVariantModel
                        ->where('product_id', $product['id'])
                        ->where('is_delete', 0)
                        ->findAll();

                    // 3) Only proceed if we actually got at least one variant
                    if (empty($variants)) {
                        // no variants → skip this product
                        continue;
                    }

                    // 4) Attach the variants array
                    $product['variants'] = $variants;

                    // 5) Default cart_quantity to zero
                    $product['cart_quantity'] = 0;

                    // 6) If the user is logged in, see if they have this in their cart
                    if (isset($user['id'])) {
                        $cartItem = $cartsModel
                            ->where('user_id', $user['id'])
                            ->where('product_id', $product['id'])
                            ->first();
                        if ($cartItem) {
                            $product['cart_quantity'] = (int) $cartItem['quantity'];
                        }
                    }

                    // 7) Finally, push into your output array
                    $products[] = $product;
                }
            }
        }

        $data['products'] = $products;
        return $data['products'];
    }

    public function categoryProducts($category_id)
    {
        $sellerModel = new SellerModel();
        $productVariantModel = new ProductVariantsModel();
        $cartsModel = new CartsModel();
        $products = [];

        // Check if session is set and get user details
        $user = null;
        if (session()->has('email')) {
            $userModel = new UserModel();
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        // Get sellers based on city ID
        $sellers = $sellerModel->where('city_id', session()->get('city_id'))->findAll();

        foreach ($sellers as $seller) {
            // Get all popular products from each seller
            $sellerProducts = $this->where('category_id', $category_id)
                ->where('is_delete', 0)
                ->where('seller_id', $seller['id'])
                ->findAll();

            foreach ($sellerProducts as $product) {
                // 2) Fetch non-deleted variants for this product
                $variants = $productVariantModel
                    ->where('product_id', $product['id'])
                    ->where('is_delete', 0)
                    ->findAll();

                // 3) Only proceed if we actually got at least one variant
                if (empty($variants)) {
                    // no variants → skip this product
                    continue;
                }

                // 4) Attach the variants array
                $product['variants'] = $variants;

                // 5) Default cart_quantity to zero
                $product['cart_quantity'] = 0;

                // 6) If the user is logged in, see if they have this in their cart
                if (isset($user['id'])) {
                    $cartItem = $cartsModel
                        ->where('user_id', $user['id'])
                        ->where('product_id', $product['id'])
                        ->first();
                    if ($cartItem) {
                        $product['cart_quantity'] = (int) $cartItem['quantity'];
                    }
                }

                // 7) Finally, push into your output array
                $products[] = $product;
            }
        }

        $data['products'] = $products;
        return $data['products'];
    }
    // Count low stock products
    public function countLowStockProducts($threshold = 10)
    {
        return $this->db->table('product_variants')
            ->join('product', 'product_variants.product_id = product.id')
            ->where('product_variants.is_unlimited_stock', 0)
            ->where('product_variants.stock >', 0)
            ->where('product_variants.stock <', $threshold)
            ->where('product_variants.is_delete', 0)
            ->countAllResults();
    }

    public function countLowStockProductsForSeller($threshold = 10)
    {
        return $this->db->table('product_variants')
            ->join('product', 'product_variants.product_id = product.id')
            ->where('product_variants.is_unlimited_stock', 0)
            ->where('product.seller_id', session()->get('user_id'))
            ->where('product_variants.stock >', 0)
            ->where('product_variants.stock <', $threshold)
            ->where('product_variants.is_delete', 0)
            ->countAllResults();
    }

    // Count out of stock products
    public function countOutOfStockProducts()
    {
        return $this->db->table('product_variants')
            ->join('product', 'product_variants.product_id = product.id')
            ->where('product_variants.is_unlimited_stock', 0)
            ->where('product_variants.stock', 0)
            ->where('product_variants.is_delete', 0)
            ->countAllResults();
    }

    public function countOutOfStockProductsForSeller()
    {
        return $this->db->table('product_variants')
            ->join('product', 'product_variants.product_id = product.id')
            ->where('product_variants.is_unlimited_stock', 0)
            ->where('product.seller_id', session()->get('user_id'))
            ->where('product_variants.stock', 0)
            ->where('product_variants.is_delete', 0)
            ->countAllResults();
    }
}
