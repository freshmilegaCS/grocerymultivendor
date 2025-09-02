<?php

namespace App\Libraries;

use App\Libraries\GeoUtils;

use App\Models\AddressModel;
use App\Models\CartsModel;
use App\Models\CouponModel;
use App\Models\DeliverableAreaModel;
use App\Models\ProductModel;
use App\Models\ProductVariantsModel;
use App\Models\TaxModel;
use App\Models\UsedCouponModel;
use App\Models\SellerModel;

class CartSummery
{
    function calculateCartTotals($userId, $seller_id = 0)
    {
        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();

        if ($seller_id == 0) {
            $cartItems = $cartsModel->where('user_id', $userId)->findAll();
        } else {
            $cartItems = $cartsModel->where('user_id', $userId)->where('seller_id', $seller_id)->findAll();
        }

        $subTotal = 0;
        $discountedPricesaving = 0;
        $taxTotal = 0;

        foreach ($cartItems as $cartItem) {
            // Fetch product and variant details
            $product = $productModel
                ->select('tax_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            $variant = $variantModel
                ->select('price, discounted_price')
                ->where('id', $cartItem['product_variant_id'])
                ->where('is_delete', 0)
                ->first();



            if (isset($variant['discounted_price']) && $variant['discounted_price'] > 0) {
                $subTotal += ($cartItem['quantity'] * (int)$variant['discounted_price']);
                $discountedPricesaving += ($cartItem['quantity'] * ($variant['price'] - (int)$variant['discounted_price']));

                if (isset($product['tax_id']) && $product['tax_id'] > 0) {
                    $tax = $taxModel->where('id', $product['tax_id'])->first();
                    if ($tax) {
                        $taxTotal += ($cartItem['quantity'] * (int)$variant['discounted_price']) * $tax['percentage'] / 100;
                    }
                }
            } else {
                $subTotal += ($cartItem['quantity'] * $variant['price']);

                if (isset($product['tax_id']) && $product['tax_id'] > 0) {
                    $tax = $taxModel->where('id', $product['tax_id'])->first();
                    if ($tax) {
                        $taxTotal += ($cartItem['quantity'] * $variant['price']) * $tax['percentage'] / 100;
                    }
                }
            }
        }

        return [$subTotal, $taxTotal, $discountedPricesaving];
    }

    function calculateDeliveryChargeForAddress($userId, $subTotal)
    {
        $geoUtils = new GeoUtils();

        $addressModel = new AddressModel();
        $address = $addressModel->where('user_id', $userId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->first();

        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $sellerModel = new SellerModel();

        $deliveryCharge = 0;
        $minAmountForFreeDelivery = 0;
        $timeToTravel = 0;

        if ($address) {
            $cartItem = $cartsModel->where('user_id', $userId)->first();
            $product = $productModel->select('seller_id')
                ->where('id', $cartItem['product_id'])
                ->where('is_delete', 0)
                ->first();

            $seller = $sellerModel->select('latitude, longitude, deliverable_area_id')
                ->where('id', $product['seller_id'])
                ->where('status', 1)
                ->first();

            if ($seller) {
                $deliverableAreaModel = new DeliverableAreaModel();
                $deliverableArea = $deliverableAreaModel->where('is_delete', 0)
                    ->where('id', $seller['deliverable_area_id'])
                    ->first();

                if ($deliverableArea) {
                    // Calculate distance between the seller and the user's address
                    $distance = $geoUtils->haversineDistance(
                        $seller['latitude'],
                        $seller['longitude'],
                        $address['latitude'],
                        $address['longitude']
                    );

                    // Calculate time to travel
                    $timeToTravel = $distance * $deliverableArea['time_to_travel'];

                    // Calculate delivery charge
                    if ($subTotal <= $deliverableArea['min_amount_for_free_delivery']) {
                        if ($deliverableArea['delivery_charge_method'] === 'fixed_charge') {
                            $deliveryCharge = round((float)$deliverableArea['fixed_charge'], 2);
                        } elseif ($deliverableArea['delivery_charge_method'] === 'per_km_charge') {
                            $deliveryCharge = round((float)$distance * $deliverableArea['per_km_charge'], 2);
                        } elseif ($deliverableArea['delivery_charge_method'] === 'range_wise_charges') {
                            // Parse the range-wise charges JSON
                            $rangeWiseCharges = json_decode($deliverableArea['range_wise_charges'], true);
                            foreach ($rangeWiseCharges as $range) {
                                $fromRange = (float)$range['from_range'];
                                $toRange = (float)$range['to_range'];
                                $price = (float)$range['price'];

                                // Check if the distance falls within the current range
                                if ((int)$distance >= $fromRange && (int)$distance <= $toRange) {
                                    $deliveryCharge = round($price, 2);
                                    break;
                                }
                            }
                        }
                    }

                    $minAmountForFreeDelivery = $deliverableArea['min_amount_for_free_delivery'];
                }
            }
        }

        return [
            'deliveryCharge' => $deliveryCharge,
            'minAmountForFreeDelivery' => $minAmountForFreeDelivery,
            'timeToTravel' => $timeToTravel
        ];
    }

    // function calculateCouponAmount($appliedCoupon, $subTotal, $userId)
    // {
    //     $coupon_amount = 0;
    //     $coupon_id = 0;
    //     if (!is_null($appliedCoupon)) {
    //         $couponModel = new CouponModel();
    //         $usedCouponModel = new UsedCouponModel();

    //         $coupon_id = $appliedCoupon['coupon_id'];

    //         $coupon = $couponModel
    //             ->where('id', $appliedCoupon['coupon_id'])
    //             ->where('is_delete', 0)
    //             ->where('status', 1)
    //             ->first();

    //         // Fetch used coupon entry
    //         $usedCoupon = $usedCouponModel
    //             ->where('coupon_id', $coupon['id'])
    //             ->where('user_id', $userId)
    //             ->first();

    //         // Validate coupon conditions
    //         $today = date('Y-m-d');

    //         if ($coupon['is_multitimes'] == 1 && $today <= $coupon['date'] && (!$usedCoupon || $usedCoupon['user_id'] == $userId)) {
    //             if ($subTotal >= $coupon['min_order_amount']) {
    //                 $coupon_amount = $coupon['value'];
    //             }
    //         }
    //     }
    //     return [$coupon_amount, $coupon_id];
    // }

    function calculateCouponAmount($appliedCoupon, $subTotal, $userId)
    {
        $coupon_amount = 0;
        $coupon_id = 0;

        if (!is_null($appliedCoupon)) {
            $couponModel = new CouponModel();
            $usedCouponModel = new UsedCouponModel();

            $coupon_id = $appliedCoupon['coupon_id'];

            $coupon = $couponModel
                ->where('id', $appliedCoupon['coupon_id'])
                ->where('is_delete', 0)
                ->where('status', 1)
                ->first();

            // Fetch used coupon entry
            $usedCoupon = $usedCouponModel
                ->where('coupon_id', $coupon['id'])
                ->where('user_id', $userId)
                ->first();

            // Validate coupon conditions
            $today = date('Y-m-d');

            if ($coupon['is_multitimes'] == 1 && $today <= $coupon['date'] && (!$usedCoupon || $usedCoupon['user_id'] == $userId)) {
                if ($subTotal >= $coupon['min_order_amount']) {
                    // Calculate coupon amount based on coupon type
                    if ($coupon['coupon_type'] == '1') {
                        // Percentage discount: calculate percentage of subtotal
                        $coupon_amount = ($subTotal * $coupon['value']) / 100;
                    } else if ($coupon['coupon_type'] == '2') {
                        // Fixed value discount
                        $coupon_amount = $coupon['value'];
                    }
                }
            }
        }

        return [$coupon_amount, $coupon_id];
    }

    function calculateWalletAmount($data, $walletBalance, $total)
    {
        // Initialize default response values
        $walletApplied = 0;
        $remainingWalletBalance = $walletBalance;

        // Check if wallet data is provided and contains a valid wallet_applied key
        if (isset($data['wallet']) && isset($data['wallet']['wallet_applied'])) {
            $requestedWalletApplied = (float)$data['wallet']['wallet_applied'];

            // Ensure the requested wallet amount doesn't exceed the user's wallet balance
            if ($requestedWalletApplied > $walletBalance) {
                // Invalid case: requested more than available balance
                $walletApplied = $walletBalance;
                $remainingWalletBalance = 0;
            } else {
                // Requested wallet amount is valid
                $walletApplied = $requestedWalletApplied;

                // Check if the requested amount exceeds the total
                if ($walletApplied > $total) {
                    $walletApplied = $total; // Use only the amount needed to cover the total
                    $remainingWalletBalance = $walletBalance - $walletApplied;
                    $total = 0;
                } else {
                    // Reduce the total and adjust wallet balance accordingly
                    $total -= $walletApplied;
                    $remainingWalletBalance = $walletBalance - $walletApplied;
                }
            }
        } elseif (!isset($data['wallet'])) {
            // If wallet data is not sent, do not apply wallet balance
            $walletApplied = 0;
            $remainingWalletBalance = $walletBalance;
        }

        return [$walletApplied, $remainingWalletBalance];
    }
}
