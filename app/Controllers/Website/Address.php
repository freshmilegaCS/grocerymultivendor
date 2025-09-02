<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;

use App\Models\AddressModel;
use App\Models\CartsModel;
use App\Models\CityModel;
use App\Models\DeliverableAreaModel;
use App\Models\ProductModel;
use App\Models\UserModel;

use App\Libraries\GeoUtils;
use App\Libraries\CartSummery;
use App\Models\SellerModel;

class Address extends BaseController
{
    public function saveAddress()
    {
        $geoUtils = new GeoUtils();
        $cartSummery = new CartSummery();
        $data = $this->request->getJSON(true);

        // Fetch the current user from session
        $userModel = new UserModel();
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


        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }

        // Fetch the city details
        $cityModel = new CityModel();
        $city = $cityModel->where('name', $data['city'])
            ->where('is_delete', 0)
            ->first();



        // Fetch deliverable areas
        $deliverableAreaModel = new DeliverableAreaModel();
        $areas = $deliverableAreaModel->where('is_delete', 0)->findAll();

        // Calculate cart totals
        list($subTotal, $taxTotal,) = $cartSummery->calculateCartTotals($user['id']);

        foreach ($areas as $area) {
            $polygon = json_decode($area['boundary_points_web'], true);

            // Convert boundary points to a usable array
            $polygonPoints = array_map(fn($point) => [$point['lat'], $point['lng']], $polygon);

            // Check if the provided lat/lng is inside the polygon
            if ($geoUtils->pointInPolygon($data['latitude'], $data['longitude'], $polygonPoints)) {
                // Update existing addresses for the user to status = 0
                $addressModel = new AddressModel();
                $addressModel->where('user_id', $user['id'])->set(['status' => 0])->update();

                // Prepare the new address data
                $addressData = [
                    'user_id' => $user['id'],
                    'city_id' => $city['id'] ?? 0,
                    'address' => $data['address'],
                    'area' => $data['area'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'status' => 1,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'map_address' => $data['map_address'],
                    'is_delete' => 0,
                    'deliverable_area_id' => $area['id'],
                    'address_type' => $data['address_type'],
                    'flat' => $data['flat'],
                    'floor' => $data['floor'],
                    'user_name' => $data['user_name'],
                    'user_mobile' => $data['user_mobile']
                ];

                $cartsModel = new CartsModel();
                $cartItem = $cartsModel->where('user_id', $user['id'])->first();

                $deliveryCharge = 0;
                $minAmountForFreeDelivery = 0;
                $timeToTravel = 0;

                if ($cartItem) {
                    $productModel = new ProductModel();
                    $sellerModel = new SellerModel();

                    $product = $productModel->select('seller_id')
                        ->where('id', $cartItem['product_id'])
                        ->where('is_delete', 0)
                        ->first();

                    $seller = $sellerModel->select('latitude, longitude, deliverable_area_id')
                        ->where('id', $product['seller_id'])
                        ->where('status', 1)
                        ->first();

                    $deliverableAreaModel = new DeliverableAreaModel();
                    $deliverableArea = $deliverableAreaModel->where('is_delete', 0)
                        ->where('id', $seller['deliverable_area_id'])
                        ->first();

                    $distance = $geoUtils->haversineDistance(
                        $seller['latitude'],
                        $seller['longitude'],
                        $data['latitude'],
                        $data['longitude']
                    );

                    $timeToTravel = $distance * $deliverableArea['time_to_travel'];

                    // Calculate delivery charge
                    if ($subTotal <= $deliverableArea['min_amount_for_free_delivery']) {
                        if ($deliverableArea['delivery_charge_method'] === 'fixed_charge') {
                            $deliveryCharge = $deliverableArea['fixed_charge'];
                        } elseif ($deliverableArea['delivery_charge_method'] === 'per_km_charge') {
                            $deliveryCharge = $distance * $deliverableArea['per_km_charge'];
                        } elseif ($deliverableArea['delivery_charge_method'] === 'range_wise_charges') {
                            $rangeWiseCharges = json_decode($deliverableArea['range_wise_charges'], true);
                            foreach ($rangeWiseCharges as $range) {
                                if ($distance >= (float)$range['from_range'] && $distance <= (float)$range['to_range']) {
                                    $deliveryCharge = (float)$range['price'];
                                    break;
                                }
                            }
                        }
                    }

                    $minAmountForFreeDelivery = $deliverableArea['min_amount_for_free_delivery'];
                } else {
                    // Handle no cart items scenario
                    $deliveryCharge = 0;
                    $minAmountForFreeDelivery = $area['min_amount_for_free_delivery'];
                    $timeToTravel = 0; // Default to 0 if no cart items exist
                }

                // Insert the new address
                if ($addressModel->insert($addressData)) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Address saved successfully',
                        'deliveryCharge' => $deliveryCharge,
                        'min_amount_for_free_delivery' => $area['min_amount_for_free_delivery'],
                        'time_to_travel' => $timeToTravel,
                        'minAmountForFreeDelivery' => $minAmountForFreeDelivery,
                        'subTotal' => $subTotal,
                        'taxTotal' => $taxTotal
                    ]);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save address. Please try again later.']);
                }
            }
        }

        // If no area matches
        return $this->response->setJSON(['status' => 'error', 'message' => 'Address is not in a deliverable area']);
    }

    public function deleteAddress()
    {
        $data = $this->request->getJSON(true);

        $userModel = new UserModel();
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

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found or inactive.']);
        }

        $addressModel = new AddressModel();

        // Fetch the address to verify its status
        $address = $addressModel->where('user_id', $user['id'])
            ->where('id', $data['address_id'])
            ->where('is_delete', 0)
            ->first();

        if (!$address) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Address not found or already deleted.']);
        }

        // Check if the address has `status = 1`
        if ($address['status'] == 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unable to delete an active address.']);
        }

        // Update the address to mark it as deleted
        $updated = $addressModel->where('user_id', $user['id'])
            ->where('id', $data['address_id'])
            ->where('status', 0)
            ->set(['is_delete' => 1])
            ->update();

        if ($updated) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Address deleted successfully.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete address. Please try again later.']);
        }
    }



    public function activeAddress()
    {
        $cartSummery = new CartSummery();

        $data = $this->request->getJSON(true);

        $userModel = new UserModel();
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

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found or inactive.']);
        }

        $addressModel = new AddressModel();

        // Validate the address
        $address = $addressModel->where('user_id', $user['id'])
            ->where('id', $data['address_id'])
            ->where('is_delete', 0)
            ->first();

        if (!$address) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Address not found or deleted.']);
        }

        // Deactivate all addresses for the user
        $deactivate = $addressModel
            ->where('user_id', $user['id'])
            ->set(['status' => 0])
            ->update();

        // Activate the selected address
        $activate = $addressModel
            ->where('id', $data['address_id'])
            ->where('user_id', $user['id'])
            ->set(['status' => 1])
            ->update();

        // Calculate subtotal, tax, and delivery charges
        list($subTotal) = $cartSummery->calculateCartTotals($user['id']);

        $cartSummery->calculateDeliveryChargeForAddress($user['id'], $subTotal);


        // Check both operations
        if ($deactivate && $activate) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Address activated successfully.',
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to activate address. Please try again.',
            ]);
        }
    }

    public function fetchAddressList()
    {
        $userModel = new UserModel();
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

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found or inactive.'
            ]);
        }

        $addressModel = new AddressModel();

        $addressesWithInActiveStatus = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 0)
            ->orderBy('id', 'DESC')
            ->findAll(2);

        $addressWithActiveStatus = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        return $this->response->setJSON([
            'status' => 'success',
            'addressesWithInActiveStatus' => $addressesWithInActiveStatus,
            'addressWithActiveStatus' => $addressWithActiveStatus
        ]);
    }

    public function index()
    {
        $data['settings'] = $this->settings;
        $data['country'] = $this->country;
        $cartsModel = new CartsModel();
        $userModel = new UserModel();
        if (session()->get('login_type') == 'email') {
            $user = $userModel->where('email', session()->get('email'))->where('is_active', 1)->where('is_delete', 0)->first();
        }

        if (session()->get('login_type') == 'mobile') {
            $user = $userModel->where('mobile', session()->get('mobile'))->where('is_active', 1)->where('is_delete', 0)->first();
        }
        if (!isset($user)) {
            return redirect()->to('/');
        } else {
            $cartItemCount = $cartsModel->where('user_id', $user['id'])->countAllResults();
            $data['cartItemCount'] = $cartItemCount;
            $data['user'] = $user;
        }


        $data['user_name'] = $user['name'];
        $data['user_mobile'] = $user['mobile'];
        $data['user_email'] = $user['email'];

        return view('website/address/address', $data);
    }

    public function fetchAllAddressList()
    {
        $userModel = new UserModel();
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

        if (!$user) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found or inactive.'
            ]);
        }

        $addressModel = new AddressModel();

        $addressesWithInActiveStatus = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 0)
            ->orderBy('id', 'DESC')
            ->findAll();

        $addressWithActiveStatus = $addressModel->where('user_id', $user['id'])
            ->where('is_delete', 0)
            ->where('status', 1)
            ->first();

        return $this->response->setJSON([
            'status' => 'success',
            'addressesWithInActiveStatus' => $addressesWithInActiveStatus,
            'addressWithActiveStatus' => $addressWithActiveStatus
        ]);
    }
}
