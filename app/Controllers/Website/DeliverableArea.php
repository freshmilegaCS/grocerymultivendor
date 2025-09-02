<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\DeliverableAreaModel;

use App\Libraries\GeoUtils;
use App\Models\CartsModel;
use App\Models\ProductModel;
use App\Models\ProductVariantsModel;
use App\Models\TaxModel;
use App\Models\UserModel;

class DeliverableArea extends BaseController
{
    public function fetchIsInDeliveryArea()
    {
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        // Get input data
        $dataInput = $this->request->getJSON(true);
        $latitude = $dataInput['lat'];
        $longitude = $dataInput['lng'];

        // Load deliverable area model
        $deliverableAreaModel = new DeliverableAreaModel();
        $areas = $deliverableAreaModel->where('is_delete', 0)->findAll();

        foreach ($areas as $area) {
            // Calculate delivery charge for the current area
            $subTotal = $this->calculateCartTotals($user['id'])[0]; // Assuming this returns an array
            $chargeDetails = $this->calculateDeliveryChargeForAddress($latitude, $longitude, $area, $subTotal);

            if ($chargeDetails) {
                // Delivery available in the area
                $deliveryCharge = $chargeDetails['deliveryCharge'];
                $nearestDistance = $chargeDetails['nearestDistance'];
                $timeToTravel = $nearestDistance * $area['time_to_travel'];

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Delivery available in this area.',
                    'deliveryCharge' => $deliveryCharge,
                    'time_to_travel' => $timeToTravel,
                    'nearestDistance' => $nearestDistance
                ]);
            }
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'We are not available at this location at the moment.']);
    }

    public function calculateDeliveryChargeForAddress($latitude, $longitude, $area, $subTotal)
    {
        $geoUtils = new GeoUtils();
        $polygon = json_decode($area['boundary_points_web'], true);

        // Convert each lat/lng to an array format for the pointInPolygon function
        $polygonPoints = array_map(function ($point) {
            return [$point['lat'], $point['lng']];
        }, $polygon);

        // Check if the current location is inside this polygon
        if ($geoUtils->pointInPolygon($latitude, $longitude, $polygonPoints)) {
            $deliveryCharge = 0;
            $nearestDistance = $geoUtils->calculateDistanceToNearestPoint($latitude, $longitude, $polygonPoints);

            if ($subTotal <= $area['min_amount_for_free_delivery']) {
                // Calculate delivery charge based on the method
                if ($area['delivery_charge_method'] === 'fixed_charge') {
                    $deliveryCharge = $area['fixed_charge'];
                } elseif ($area['delivery_charge_method'] === 'per_km_charge') {
                    $deliveryCharge = $nearestDistance * $area['per_km_charge'];
                } elseif ($area['delivery_charge_method'] === 'range_wise_charges') {

                    $rangeWiseCharges = json_decode($area['range_wise_charges'], true);
                        foreach ($rangeWiseCharges as $range) {
                            $fromRange = (float)$range['from_range'];
                            $toRange = (float)$range['to_range'];
                            $price = (float)$range['price'];

                            // Check if the distance falls within the current range
                            if ($nearestDistance >= $fromRange && $nearestDistance <= $toRange) {
                                $deliveryCharge = $price;
                                break;
                            }
                        }

                }
            }

            return [
                'deliveryCharge' => $deliveryCharge,
                'nearestDistance' => $nearestDistance
            ];
        }

        return null;
    }


    private function calculateCartTotals($userId)
    {
        $cartsModel = new CartsModel();
        $productModel = new ProductModel();
        $variantModel = new ProductVariantsModel();
        $taxModel = new TaxModel();

        $cartItems = $cartsModel->where('user_id', $userId)->findAll();

        $subTotal = 0;
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

            $price = (int)$variant['discounted_price'] ?: (int)$variant['price'];
            $subTotal += $cartItem['quantity'] * $price;

            // Calculate tax if applicable
            if ($product && $product['tax_id']) {
                $tax = $taxModel->where('id', $product['tax_id'])->first();
                $taxTotal += ($price * $tax['percentage'] / 100) * $cartItem['quantity'];
            }
        }

        return [$subTotal, $taxTotal];
    }
}
