<?php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\CartsModel;
use App\Models\CityModel;
use App\Models\DeliverableAreaModel;
use App\Models\ProductModel;
use App\Models\SellerModel;
use App\Models\UserModel;
use App\Libraries\GeoUtils;

class City extends BaseController
{
    private function pointInPolygon($testLat, $testLng, $boundaryPoints)
    {
        $n = count($boundaryPoints);
        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $lat1 = $boundaryPoints[$i]['latitude'];
            $lng1 = $boundaryPoints[$i]['longitude'];
            $lat2 = $boundaryPoints[$j]['latitude'];
            $lng2 = $boundaryPoints[$j]['longitude'];

            // Check if point is on the same side
            if ((($lng1 > $testLng) != ($lng2 > $testLng)) &&
                ($testLat < ($lat2 - $lat1) * ($testLng - $lng1) / ($lng2 - $lng1) + $lat1)
            ) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    public function fetchDeliverableAreaByLatLong()
    {
        $dataInput = $this->request->getJSON(true);

        $latitude = $dataInput['lat'];
        $longitude = $dataInput['lng'];

        // Load the DeliverableArea model
        $deliverableAreaModel = new DeliverableAreaModel();
        $sellerModel = new SellerModel();
        $productModel = new ProductModel();

        // Fetch all deliverable areas
        $deliverableAreas = $deliverableAreaModel->where('is_delete', 0)->findAll();

        $foundArea = null;
        $cityId = 0;
        $deliverableAreaId = 0;
        $productExist = false;

        // Loop through each deliverable area to check if the point is inside the polygon
        foreach ($deliverableAreas as $area) {
            $boundaryPoints = json_decode($area['boundry_points'], true);

            // Ensure boundaryPoints is valid
            if (!is_array($boundaryPoints) || empty($boundaryPoints)) {
                continue;
            }

            if ($this->pointInPolygon($latitude, $longitude, $boundaryPoints)) {
                $foundArea = $area;
                $cityId = $area['city_id'];
                $deliverableAreaId = $area['id'];

                // Check if sellers exist in this area
                $findSellers = $sellerModel->select('COUNT(id) as total_sellers')
                    ->where('deliverable_area_id', $area['id'])
                    ->where('is_delete', 0)
                    ->where('status', 1)
                    ->first();

                if ($findSellers && $findSellers['total_sellers'] > 0) {
                    // Get list of seller IDs
                    $findSellersForProduct = $sellerModel->select('id')
                        ->where('deliverable_area_id', $area['id'])
                        ->where('is_delete', 0)
                        ->where('status', 1)
                        ->findAll();

                    $sellerIds = array_column($findSellersForProduct, 'id');

                    if (!empty($sellerIds)) {
                        //empty cart
                        $guestId = isset($dataInput['guest_id']) ? $dataInput['guest_id'] : null;
                        $cartsModel = new CartsModel();

                        if ($area['city_id'] != session()->get('city_id')) {
                            if (session()->has('email') || session()->has('mobile')) {
                                $userModel = new UserModel();
                                $user = null;

                                if (session()->get('login_type') == 'email') {
                                    $user = $userModel->where('email', session()->get('email'))
                                        ->where('is_active', 1)
                                        ->where('is_delete', 0)
                                        ->first();
                                } elseif (session()->get('login_type') == 'mobile') {
                                    $user = $userModel->where('mobile', session()->get('mobile'))
                                        ->where('is_active', 1)
                                        ->where('is_delete', 0)
                                        ->first();
                                }

                                if ($user) {
                                    $cartsModel->where('user_id', $user['id'])->delete();
                                }
                            } elseif ($guestId) {
                                $cartsModel->where('guest_id', $guestId)->delete();
                            }
                        }



                        // Count products available under these sellers
                        $ProductCount = $productModel->select('COUNT(id) as total_product')
                            ->whereIn('seller_id', $sellerIds)
                            ->where('is_delete', 0) // 
                            ->where('status', 1) // 
                            ->first();

                        if ($ProductCount && $ProductCount['total_product'] > 0) {
                            $productExist = true;
                            break; // 
                        }
                    }
                }
            }
        }

        if ($foundArea && $productExist) {
            session()->set('city_id', $cityId);
            session()->set('deliverable_area_id', $deliverableAreaId);
            
                $findFirstSeller = $sellerModel->select('id, latitude, longitude')
                    ->where('deliverable_area_id', $deliverableAreaId)
                    ->where('is_delete', 0)
                    ->where('status', 1)
                    ->first();
                $perKmTime = $deliverableAreaModel->where('is_delete', 0)->where('id', $deliverableAreaId)->first();
        
                $geoUtils = new GeoUtils();
                $findTime = $geoUtils->travelDistanceTime($latitude, $longitude, $findFirstSeller['latitude'], $findFirstSeller['longitude'], $perKmTime['time_to_travel']);
                if ($findTime) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Location is within a deliverable area.',
                        'id' => $cityId,
                        'deliverable_area_id' => $deliverableAreaId,
                        'delivery_time' => $perKmTime['base_delivery_time'] + $findTime['estimated_delivery_time_min'],
                        'distance_km' => $findTime['distance_km']
                    ]);
                } else {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Location is within a deliverable area.',
                        'id' => $cityId,
                        'deliverable_area_id' => $deliverableAreaId,
                        'delivery_time' => null,
                        'distance_km' => null
                    ]);
                }

        } else {
            session()->set('city_id', 0);
            session()->set('deliverable_area_id', 0);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'We are not serviceable at your location.',
                'id' => 0,
                'deliverable_area_id' => 0,
                'delivery_time' => null,
                'distance_km' => null
            ]);
        }
    }

    public function getCityId()
    {
        $input = $this->request->getJSON();
        $cityName = $input->name ?? '';
        $cityModel = new CityModel();
        $city = $cityModel->where('name', $cityName)->where('is_delete', 0)->first();

        if ($city) {
            $session = session();
            $session->set('city_id', $city['id']);
            return $this->respond([
                'id' => $city['id']
            ]);
        } else {
            return $this->respond([
                'id' => 0
            ]);
        }
    }

    public function testPointInPolygon()
    {
        // $boundaryPoints = [
        //     ["latitude" => 21., "longitude" => 79.],
        //     ["latitude" => 21., "longitude" => 78.],
        //     ["latitude" => 21., "longitude" => 79.]        
        // ];


        $boundaryPoints = [["latitude" => 21.215923453955355, "longitude" => 79.05875270041984], ["latitude" => 21.127562295450463, "longitude" => 78.95644251975578], ["latitude" => 21.06478106239201, "longitude" => 79.08759181174797], ["latitude" => 21.131405181855662, "longitude" => 79.20157496604484], ["latitude" => 21.18647559981752, "longitude" => 79.1603762355761], ["latitude" => 21.20632153794038, "longitude" => 79.10750453147453]];

        $testLat = 21.1458;
        $testLng = 79.088155;

        $result = $this->pointInPolygon($testLat, $testLng, $boundaryPoints);

        return $this->response->setJSON([
            'point' => ['lat' => $testLat, 'lng' => $testLng],
            'inside_polygon' => $result,
            'message' => $result ? 'Point is inside polygon' : 'Point is outside polygon'
        ]);
    }
}
