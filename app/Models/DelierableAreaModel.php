<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\CityModel;


class DelierableAreaModel extends Model
{
    protected $table = 'deliverable_area';
    protected $primaryKey = 'id';
    protected $allowedFields = ['city_id', 'deliverable_area_title', 'boundry_points', 'radius', 'geolocation_type', 'is_delete', 'boundary_points_web', 'min_amount_for_free_delivery', 'delivery_charge_method', 'fixed_charge', 'per_km_charge', 'range_wise_charges', 'time_to_travel', 'max_deliverable_distance', 'base_delivery_time'];

    public function index()
    {
        $cityModel = new CityModel();
        return $data['city'] = $cityModel->findAll();
    }
    public function view()
    {

        return $this->where('is_delete', 0)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function add($boundary_points, $boundary_points_web, $edit_city, $radius, $geolocation_type, $deliverable_area,$time_to_travel,$min_amount_for_free_delivery, $delivery_charge_method, $delivery_charge, $base_delivery_time
)
    {
        $data = [
            'boundry_points' => $boundary_points,
            'boundary_points_web' => $boundary_points_web,
            'city_id' => $edit_city,
            'radius' => $radius,
            'geolocation_type' => $geolocation_type,
            'deliverable_area_title' => $deliverable_area,
            'time_to_travel' => $time_to_travel,
            'min_amount_for_free_delivery' => $min_amount_for_free_delivery,
            'delivery_charge_method' => $delivery_charge_method,
            $delivery_charge_method => $delivery_charge,
            'base_delivery_time' => $base_delivery_time

        ];
        return $this->insert($data);
    }
    public function updateCity($city_id, $longitude, $latitude, $city_name)
    {
        $data = [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'name' => $city_name,
        ];
        return $this->set($data)
            ->where('id', $city_id)
            ->update();
    }
    public function deleteCity($city_id)
    {
        return $this->where('id', $city_id)->delete();
    }
    public function getDeliverableAreaById($deliverableAreaId)
    {
        return $this->where('id', $deliverableAreaId)
                    ->first();
    }
    public function getDeliverableAreasByCity($city_id)
    {
        $result = $this->where(['city_id' => $city_id, 'is_delete' => 0])
                      ->findAll();

        

        return $result;
    }
    public function getDeliverableAreaCount()
    {
        return $this->where('is_delete', 0)
                    ->countAllResults();
    }
}
