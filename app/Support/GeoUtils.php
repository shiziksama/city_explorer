<?php

namespace App\Support;

class GeoUtils
{
    /**
     * Calculate the haversine distance between two coordinates in meters.
     */
    public static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        return (int) ceil(12745594 * asin(
            sqrt(
                pow(sin(deg2rad($lat2 - $lat1) / 2), 2) +
                cos(deg2rad($lat1)) *
                cos(deg2rad($lat2)) *
                pow(sin(deg2rad($lng2 - $lng1) / 2), 2)
            )
        ));
    }
}
