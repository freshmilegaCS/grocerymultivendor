<?php

namespace App\Libraries;

class GeoUtils
{
    public function pointInPolygon($lat, $lng, $polygon)
    {
        $inside = false;
        $n = count($polygon);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];

            $intersect = (($yi > $lng) != ($yj > $lng)) &&
                ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }
        return $inside;
    }

    // Haversine formula to calculate distance between two lat/lng points
    public function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $lat1 = (float) $lat1;
        $lng1 = (float) $lng1;
        $lat2 = (float) $lat2;
        $lng2 = (float) $lng2;
    
        $earthRadius = 6371; // Radius of the earth in km
    
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
    
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
    
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Distance in km
    
        return $distance;
    }

    // Calculate the distance to the nearest point in the polygon
    public function calculateDistanceToNearestPoint($lat, $lng, $polygon)
    {
        $minDistance = PHP_FLOAT_MAX;

        foreach ($polygon as $point) {
            $distance = $this->haversineDistance($lat, $lng, $point[0], $point[1]);
            if ($distance < $minDistance) {
                $minDistance = $distance;
            }
        }

        return $minDistance; // Distance in km
    }

    public function travelDistanceTime($lat1, $lng1, $lat2, $lng2, $timePerKm = 5)
    {
        $earthRadius = 6371; // Radius of the earth in km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c; // Distance in km

        // Calculate estimated delivery time
        $deliveryTime = $distance * $timePerKm; // Time in minutes

        return [
            'distance_km' => round($distance, 2),
            'estimated_delivery_time_min' => round($deliveryTime)
        ];
    }
}
