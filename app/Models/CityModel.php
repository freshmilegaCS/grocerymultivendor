<?php

namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table = 'city';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'latitude', 'longitude', 'is_delete'];
    public function getAllCity()
    {
        return $this->where('is_delete', 0)->findAll();
    }

    public function addCity(
        $longitude,
        $latitude,
        $city_name
    ) {
        $data = [
            'longitude' => $longitude,
            'latitude' => $latitude,
            'name' => $city_name,
        ];
        return $this->insert($data);
    }
    public function updateCity(
        $city_id,
        $longitude,
        $latitude,
        $city_name
    ) {
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
        $data = [
            'is_delete' => 1
        ];
        return $this->set($data)
            ->where('id', $city_id)
            ->update();
    }
    public function getCityList($searchQuery)
    {
        return $this->select('*')
            ->like('name', $searchQuery)
            ->where('is_delete', 0)
            ->findAll();
    }

    public function getDeliverableAreas($cityId)
    {
        return $this->db->table('deliverable_area')
            ->select('id, boundry_points')
            ->where('city_id', $cityId)
            ->where('is_delete', 0)
            ->get()
            ->getResultArray();
    }
    public function getCityListAPI()
    {
        return $this->where('is_delete', 0)->findAll();
    }
    public function getCityById($cityId)
    {
        return $this->select('name')
            ->where('id', $cityId)
            ->first();
    }
}
