<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\CartsModel;
use App\Models\ProductModel;
use App\Models\ProductVariantsModel;
use App\Models\TaxModel;
use App\Models\UserModel;
use App\Models\SellerModel;

class Cart extends BaseController
{
    public function addToCart()
    {
        $dataInput = $this->request->getJSON(true);
        $guestId = $dataInput['guest_id'] ?? null;

        // Validate guest ID for non-logged-in users
        if (!session()->has('email') && !session()->has('mobile') && empty($guestId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for non-logged-in users.']);
        }

        // Load settings and country
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        // Fetch logged-in user
        $userModel = new UserModel();
        $user = null;

        if (session()->has('email')) {
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        } elseif (session()->has('mobile')) {
            $user = $userModel->where('mobile', session()->get('mobile'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        if (!$user && (session()->has('email') || session()->has('mobile'))) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found. Please log in to continue.']);
        }

        $userId = $user['id'] ?? 0;
        $identifier = $userId ?: $guestId;

        // Fetch product and variant details
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();

        $product = $productModel->select('id, total_allowed_quantity, slug, seller_id')->find($dataInput['product_id']);
        $variant = $variantModel->select('id, product_id, stock, is_unlimited_stock, discounted_price')
            ->where('id', $dataInput['variant_id'])
            ->first();

        if (!$product || !$variant || $variant['product_id'] != $product['id']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product or variant not found.']);
        }

        $totalAllowedQty = $product['total_allowed_quantity'];
        $availableStock = $variant['is_unlimited_stock'] ? PHP_INT_MAX : $variant['stock'];

        $cartsModel = new CartsModel();

        // Fetch existing cart item
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $existingCartItem = $cartsModel
                ->groupStart()
                ->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->groupEnd()
                ->where('product_id', $dataInput['product_id'])
                ->where('product_variant_id', $dataInput['variant_id'])
                ->where('seller_id', $product['seller_id'])
                ->first();
        } else {
            $existingCartItem = $cartsModel
                ->groupStart()
                ->where($userId ? 'user_id' : 'guest_id', $identifier)
                ->groupEnd()
                ->where('product_id', $dataInput['product_id'])
                ->where('product_variant_id', $dataInput['variant_id'])
                ->first();
        }


        $newQuantity = $existingCartItem ? $existingCartItem['quantity'] + 1 : 1;

        // Validate quantity limits
        if ($totalAllowedQty > 0 && $newQuantity > $totalAllowedQty) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'You cannot add more of this item.']);
        }

        if ($variant['is_unlimited_stock'] == 0 && $newQuantity > $availableStock) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Insufficient stock for this item.']);
        }

        // Add or update cart item
        if ($existingCartItem) {
            $cartsModel->update($existingCartItem['id'], [
                'quantity' => $newQuantity,
                'user_id' => $userId ?: $existingCartItem['user_id'],
            ]);
        } else {
            $cartsModel->insert([
                'user_id' => $userId,
                'guest_id' => $guestId,
                'product_id' => $dataInput['product_id'],
                'product_variant_id' => $dataInput['variant_id'],
                'quantity' => $newQuantity,
                'save_for_later' => 0,
                'seller_id' => $product['seller_id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Calculate cart details
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $cartItems = $cartsModel->where($userId > 0 ? 'user_id' : 'guest_id', $identifier)
                ->where('seller_id', $product['seller_id'])
                ->findAll();
        } else {
            $cartItems = $cartsModel->where($userId > 0 ? 'user_id' : 'guest_id', $identifier)->findAll();
        }

        $subTotal = 0;
        $discountedPricesaving = 0;

        foreach ($cartItems as $cartItem) {
            $variant = $variantModel->select('discounted_price, price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($variant) {
                $itemPrice = $variant['price'];
                $discountedPrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;

                $subTotal += $cartItem['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItem['quantity'] * ($itemPrice - $discountedPrice);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Item added to cart successfully.',
            'slug' => $product['slug'],
            'quantity' => $newQuantity,
            'itemCount' => count($cartItems),
            'subtotal' => $subTotal,
            'discountedPricesaving' => $discountedPricesaving,
            'currency_symbol' => $this->country['currency_symbol'],
            'currency_symbol_position' => $this->settings['currency_symbol_position'],
        ]);
    }

    public function cartItemList() //this fun return view
    {
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }

        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            return redirect()->to('/');
        }

        $cartsModel = new CartsModel();
        $userModel = new UserModel();

        $user = null;

        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        // Get the logged-in user's information
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return redirect()->to('/');
        }

        // Fetch cart items for the user
        $cartsModel = new CartsModel();
        $cartItems = $cartsModel->where('user_id', $user['id'])->findAll();

        // Initialize an array to hold the product data
        $productDetails = [];

        $subTotal = 0;
        $discountedPricesaving = 0;
        $taxTotal = 0;

        foreach ($cartItems as $cartItem) {
            // Fetch product details
            $productModel = new ProductModel();
            $product = $productModel->select('product_name, main_img, is_delete, slug, tax_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            // Fetch product variant details
            $variantModel = new ProductVariantsModel();
            $variant = $variantModel->select('id, title, price, discounted_price, stock, is_unlimited_stock, is_delete')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            // Only include items if both product and variant exist and are active
            if ($product && $variant) {
                $productDetails[] = [
                    'cart_id' => $cartItem['id'],
                    'product_name' => $product['product_name'],
                    'main_img' => $product['main_img'],
                    'slug' => $product['slug'],
                    'quantity' => $cartItem['quantity'],
                    'variant_title' => $variant['title'],
                    'price' => $variant['price'],
                    'discounted_price' => $variant['discounted_price'],
                    'stock' => $variant['is_unlimited_stock'] == 0 ? PHP_INT_MAX : $variant['stock'],
                    'product_id' => $cartItem['product_id'],
                    'product_variant_id' => $cartItem['product_variant_id']
                ];
            }

            if ((int)$variant['discounted_price']) {
                $subTotal = $subTotal + ($cartItem['quantity'] * $variant['discounted_price']);
                $discountedPricesaving = $discountedPricesaving + ($cartItem['quantity'] * ($variant['price'] - $variant['discounted_price']));
            } else {
                $subTotal = $subTotal +  ($cartItem['quantity'] * $variant['price']);
            }

            if ($product['tax_id']) {
                $taxModel = new TaxModel();
                $tax = $taxModel->where('id', $product['tax_id'])->first();
                $taxTotal = $taxTotal + ($cartItem['quantity'] * $variant['discounted_price']) * $tax['percentage'] / 100;
            }
        }

        // Pass the data to the view
        $data['productDetails'] = $productDetails;
        $data['subtotal'] = $subTotal;

        return view('website/cart/cartItemList', $data);
    }



    public function fetchCartItemList()
    {
        $dataInput = $this->request->getJSON(true);
        $guestId = $dataInput['guest_id'] ?? null;

        date_default_timezone_set($this->timeZone['timezone']);

        $userModel = new UserModel();
        $cartsModel = new CartsModel();

        $user = null;

        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        $isLoggedIn = $user !== null;
        $userId = $isLoggedIn ? $user['id'] : 0;

        if (!$isLoggedIn && !$guestId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Guest ID is required for non-logged-in users.'
            ]);
        }

        $cartCondition = $isLoggedIn ? ['user_id' => $userId] : ['guest_id' => $guestId];
        $isSingleSellerCart = $this->settings['seller_only_one_seller_cart'] == 1;

        if ($isSingleSellerCart) {
            $productIds = array_column(
                $cartsModel->select('DISTINCT(product_id)')
                    ->where($cartCondition)
                    ->findAll(),
                'product_id'
            );

            if (!empty($productIds)) {
                $productModel = new ProductModel();
                $sellerIds = array_column(
                    $productModel->select('DISTINCT(seller_id)')
                        ->whereIn('id', $productIds)
                        ->findAll(),
                    'seller_id'
                );

                if (!empty($sellerIds)) {
                    $sellerModel = new SellerModel();
                    $sellers = $sellerModel->select('id, store_name, logo')
                        ->whereIn('id', $sellerIds)
                        ->findAll();

                    $sellerItems = array_map(function ($seller) use ($cartsModel, $cartCondition) {
                        $itemCount = $cartsModel->join('product', 'product.id = carts.product_id', 'left')
                            ->where('product.seller_id', $seller['id'])
                            ->where($cartCondition)
                            ->countAllResults();

                        return [
                            'seller_id' => $seller['id'],
                            'store_name' => $seller['store_name'],
                            'logo' => base_url($seller['logo']),
                            'item_count' => $itemCount
                        ];
                    }, $sellers);

                    return $this->response->setJSON([
                        'status' => 'success',
                        'sellers' => $sellerItems
                    ]);
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'sellers' => [],
                'message' => 'No items in the cart.'
            ]);
        }

        $cartItems = $cartsModel->where($cartCondition)->findAll();
        $productItems = [];
        $subTotal = 0;
        $discountedPricesaving = 0;

        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();

        foreach ($cartItems as $cartItem) {
            $product = $productModel->select('product_name, main_img, is_delete, slug, tax_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            $variant = $variantModel->select('id, title, price, discounted_price, stock, is_unlimited_stock, is_delete')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($product && $variant) {
                $price = (int)$variant['discounted_price'] ?: $variant['price'];
                $subTotal += $cartItem['quantity'] * $price;

                if ((int)$variant['discounted_price']) {
                    $discountedPricesaving += $cartItem['quantity'] * ($variant['price'] - $variant['discounted_price']);
                }

                $productItems[] = [
                    'cart_id' => $cartItem['id'],
                    'product_name' => $product['product_name'],
                    'main_img' => base_url($product['main_img']),
                    'slug' => $product['slug'],
                    'quantity' => $cartItem['quantity'],
                    'variant_title' => $variant['title'],
                    'price' => $variant['price'],
                    'discounted_price' => $variant['discounted_price'],
                    'stock' => $variant['is_unlimited_stock'] == 1 ? PHP_INT_MAX : $variant['stock'],
                    'product_id' => $cartItem['product_id'],
                    'product_variant_id' => $cartItem['product_variant_id']
                ];
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'productItems' => $productItems,
            'subtotal' => $subTotal,
            'discountedPricesaving' => $discountedPricesaving,
            'currency_symbol' => $this->country['currency_symbol'],
            'currency_symbol_position' => $this->settings['currency_symbol_position'],
        ]);
    }


    public function removeFromCart()
    {
        $dataInput = $this->request->getJSON(true);
        $guestId = isset($dataInput['guest_id']) ? $dataInput['guest_id'] : null;

        $userModel = new UserModel();

        $user = null;
        if (session()->has('email')) {
            $user = $userModel->where('email', session()->get('email'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        } elseif (session()->has('mobile')) {
            $user = $userModel->where('mobile', session()->get('mobile'))
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->first();
        }

        $userId = $user ? $user['id'] : 0;

        if (!session()->has('email') && !session()->has('mobile') && empty($guestId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for non-logged-in users.']);
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $productModel = new ProductModel();
        $product = $productModel->select('id, total_allowed_quantity, slug, seller_id')->find($dataInput['product_id']);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        $cartsModel = new CartsModel();
        $variantModel = new ProductVariantsModel();

        // Handle cart logic based on user state
        if ($userId) {
            // Check for existing cart item by guest ID and update it to the logged-in user ID
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $guestCartItem = $cartsModel->where('guest_id', $guestId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->where('seller_id', $product['seller_id'])
                    ->first();
            } else {
                $guestCartItem = $cartsModel->where('guest_id', $guestId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->first();
            }


            if ($guestCartItem) {
                // Update guest cart item to associate with logged-in user
                $cartsModel->update($guestCartItem['id'], ['user_id' => $userId]);
            }

            // Check for existing cart item for logged-in user
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItem = $cartsModel->where('user_id', $userId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->where('seller_id', $product['seller_id'])
                    ->first();
            } else {
                $cartItem = $cartsModel->where('user_id', $userId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->first();
            }
        } else {
            // Handle guest cart items
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItem = $cartsModel->where('guest_id', $guestId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->where('seller_id', $product['seller_id'])
                    ->first();
            } else {
                $cartItem = $cartsModel->where('guest_id', $guestId)
                    ->where('product_id', $dataInput['product_id'])
                    ->where('product_variant_id', $dataInput['variant_id'])
                    ->first();
            }
        }

        if (!$cartItem) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Item not found in the cart.']);
        }

        // Calculate the new quantity
        $newQuantity = $cartItem['quantity'] - 1;

        // Update the quantity or remove the item
        if ($newQuantity > 0) {
            $cartsModel->update($cartItem['id'], ['quantity' => $newQuantity]);
        } else {
            $cartsModel->delete($cartItem['id']);
        }

        // Re-fetch all items in the cart to recalculate totals
        if ($this->settings['seller_only_one_seller_cart'] == 1) {
            $cartItems = $cartsModel->where($userId ? 'user_id' : 'guest_id', $userId ? $userId : $guestId)->where('seller_id', $product['seller_id'])->findAll();
        } else {
            $cartItems = $cartsModel->where($userId ? 'user_id' : 'guest_id', $userId ? $userId : $guestId)->findAll();
        }

        $subTotal = 0;
        $discountedPricesaving = 0;

        // Recalculate subtotal and discounted price saving
        foreach ($cartItems as $cartItemx) {
            $variant = $variantModel->select('discounted_price, price')
                ->where('id', $cartItemx['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($variant) {
                $itemPrice = $variant['price'];
                $discountedPrice = $variant['discounted_price'] > 0 ? $variant['discounted_price'] : $itemPrice;

                // Accumulate totals
                $subTotal += $cartItemx['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItemx['quantity'] * ($itemPrice - $discountedPrice);
            }
        }

        // Get the updated cart item count
        $cartItemCount = count($cartItems);

        // Return the updated response
        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newQuantity > 0 ? 'Quantity updated successfully.' : 'Item removed from the cart.',
            'slug' => $product['slug'],
            'quantity' => $newQuantity,
            'itemCount' => $cartItemCount,
            'discountedPricesaving' => $discountedPricesaving,
            'subtotal' => $subTotal,
            'currency_symbol' => $this->country['currency_symbol'],
            'currency_symbol_position' => $this->settings['currency_symbol_position'],
        ]);
    }

    public function removeItem()
    {
        // Set timezone and fetch request data
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        $dataInput = $this->request->getJSON(true);
        $guestId = isset($dataInput['guest_id']) ? $dataInput['guest_id'] : null;

        // Validate input
        if (!isset($dataInput['product_id']) || !isset($dataInput['variant_id'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product ID and Variant ID are required.']);
        }

        // Determine identifier: user_id or guest_id
        $identifier = null;
        $identifierValue = null;



        if (session()->has('email') || session()->has('mobile')) {
            $userModel = new UserModel();
            if (session()->get('login_type') == 'email') {
                $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (session()->get('login_type') == 'mobile') {
                $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (!$user) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'User not found. Please log in to continue.']);
            }

            $identifierValue = $user['id'];
        } else {
            if (!$guestId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Guest ID is required for guest users.']);
            }

            $identifierValue = $guestId;
        }

        // Initialize models
        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();

        // Check if the product exists
        $product = $productModel->select('slug, seller_id')->find($dataInput['product_id']);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found.']);
        }

        if ((int)$this->settings['seller_only_one_seller_cart'] === 1) {
            // Delete the cart item
            $deleted = $cartsModel->groupStart()
                ->where('user_id', $identifierValue)
                ->orWhere('guest_id', $guestId)
                ->groupEnd()
                ->where('product_id', $dataInput['product_id'])
                ->where('product_variant_id', $dataInput['variant_id'])
                ->where('seller_id', $product['seller_id'])
                ->delete();

            // Recalculate cart subtotal
            $cartItems = $cartsModel->groupStart()
                ->where('user_id', $identifierValue)
                ->orWhere('guest_id', $guestId)
                ->groupEnd()
                ->where('seller_id', $product['seller_id'])
                ->findAll();
        } else {
            // Delete the cart item
            $deleted = $cartsModel->groupStart()
                ->where('user_id', $identifierValue)
                ->orWhere('guest_id', $guestId)
                ->groupEnd()
                ->where('product_id', $dataInput['product_id'])
                ->where('product_variant_id', $dataInput['variant_id'])
                ->delete();

            // Recalculate cart subtotal
            $cartItems = $cartsModel->groupStart()
                ->where('user_id', $identifierValue)
                ->orWhere('guest_id', $guestId)
                ->groupEnd()
                ->findAll();
        }

        $subTotal = 0;
        $discountedPricesaving = 0;

        // Recalculate cart subtotal and savings
        foreach ($cartItems as $cartItem) {
            $variant = $variantModel->select('discounted_price, price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            if ($variant) {
                $originalPrice = (int)$variant['price'];
                $discountedPrice = (int)$variant['discounted_price'] > 0 ? $variant['discounted_price'] : $originalPrice;

                $subTotal += $cartItem['quantity'] * $discountedPrice;
                $discountedPricesaving += $cartItem['quantity'] * ($originalPrice - $discountedPrice);
            }
        }

        // Return response
        if ($deleted) {
            if ($this->settings['seller_only_one_seller_cart'] == 1) {
                $cartItemCount = $cartsModel->groupStart()
                    ->where('user_id', $identifierValue)
                    ->where('seller_id', $product['seller_id'])
                    ->orWhere('guest_id', $guestId)
                    ->groupEnd()
                    ->countAllResults();
            } else {
                $cartItemCount = $cartsModel->groupStart()
                    ->where('user_id', $identifierValue)
                    ->orWhere('guest_id', $guestId)
                    ->groupEnd()
                    ->countAllResults();
            }




            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Item removed from cart successfully.',
                'itemCount' => $cartItemCount,
                'slug' => $product['slug'],
                'discountedPricesaving' => $discountedPricesaving,
                'subtotal' => $subTotal,
                'currency_symbol' => $this->country['currency_symbol'],
                'currency_symbol_position' => $this->settings['currency_symbol_position'],
            ]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to remove item from cart.']);
        }
    }

    public function fetchCartItemCount()
    {
        $dataInput = $this->request->getJSON(true);
        $guestId = isset($dataInput['guest_id']) ? $dataInput['guest_id'] : null;

        $cartsModel = new CartsModel();

        $cartItemCount = 0;

        $user = null;

        if (session()->has('email') || session()->has('mobile')) {
            $userModel = new UserModel();
            if (session()->get('login_type') == 'email') {
                $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if (session()->get('login_type') == 'mobile') {
                $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
            }

            if ($user) {
                $cartItemCount = $cartsModel->groupStart()
                    ->where('user_id', $user['id'])
                    ->orWhere('guest_id', $guestId)
                    ->groupEnd()
                    ->countAllResults();
            }
        } elseif ($guestId) {
            $cartItemCount = $cartsModel->where('guest_id', $guestId)->countAllResults();
        }

        // Return the count, defaulting to zero if no items found
        return $this->response->setJSON([
            'status' => 'success',
            'itemCount' => $cartItemCount
        ]);
    }

    public function oneSellerCartItemList($seller_id)
    {
        // this is for onse seller cart sysytem
        // Check if user is logged in and email is verified
        if (
            (empty(session()->get('email')) || (int)session()->get('is_email_verified') !== 1) &&
            (empty(session()->get('mobile')) || (int)session()->get('is_mobile_verified') !== 1)
        ) {
            return redirect()->to('/login');
        }

        if (!$this->settings['seller_only_one_seller_cart']) {
            return redirect()->to('/');
        }

        $user = null;

        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        $cartItemCount = $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->countAllResults();
        if ($cartItemCount == 0) {
            return redirect()->to('/');
        }


        if (!$user) {
            $data['cartItemCount'] = 0;
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }

        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        date_default_timezone_set($this->timeZone['timezone']);

        // Get the logged-in user's information
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (!$user) {
            return redirect()->to('/');
        }

        // Fetch cart items for the user
        $cartsModel = new CartsModel();
        $cartItems = $cartsModel->where('user_id', $user['id'])->where('seller_id', $seller_id)->findAll();

        // Initialize an array to hold the product data
        $productDetails = [];

        $subTotal = 0;
        $discountedPricesaving = 0;
        $taxTotal = 0;

        foreach ($cartItems as $cartItem) {
            // Fetch product details
            $productModel = new ProductModel();
            $product = $productModel->select('product_name, main_img, is_delete, slug, tax_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            // Fetch product variant details
            $variantModel = new ProductVariantsModel();
            $variant = $variantModel->select('id, title, price, discounted_price, stock, is_unlimited_stock, is_delete')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();

            // Only include items if both product and variant exist and are active
            if ($product && $variant) {
                $productDetails[] = [
                    'cart_id' => $cartItem['id'],
                    'product_name' => $product['product_name'],
                    'main_img' => $product['main_img'],
                    'slug' => $product['slug'],
                    'quantity' => $cartItem['quantity'],
                    'variant_title' => $variant['title'],
                    'price' => $variant['price'],
                    'discounted_price' => $variant['discounted_price'],
                    'stock' => $variant['is_unlimited_stock'] == 0 ? PHP_INT_MAX : $variant['stock'],
                    'product_id' => $cartItem['product_id'],
                    'product_variant_id' => $cartItem['product_variant_id']
                ];
            }

            if ((int)$variant['discounted_price']) {
                $subTotal = $subTotal + ($cartItem['quantity'] * $variant['discounted_price']);
                $discountedPricesaving = $discountedPricesaving + ($cartItem['quantity'] * ($variant['price'] - $variant['discounted_price']));
            } else {
                $subTotal = $subTotal +  ($cartItem['quantity'] * $variant['price']);
            }

            if ($product['tax_id']) {
                $taxModel = new TaxModel();
                $tax = $taxModel->where('id', $product['tax_id'])->first();
                $taxTotal = $taxTotal + ($cartItem['quantity'] * $variant['discounted_price']) * $tax['percentage'] / 100;
            }
        }

        // Pass the data to the view
        $data['productDetails'] = $productDetails;
        $data['subtotal'] = $subTotal;

        $data['seller_id'] = $seller_id;

        return view('website/cart/oneSellerCartItemList', $data);
    }
}
