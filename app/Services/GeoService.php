<?php

namespace App\Services;

class GeoService
{
    /**
     * Decode a Google encoded polyline string.
     *
     * @return array<int, array{0: float, 1: float}> Coordinates in [lon, lat] order
     */
    public static function decodePolyline(string $polyline): array
    {
        $length = strlen($polyline);
        $index = 0;
        $lat = 0;
        $lng = 0;
        $points = [];

        while ($index < $length) {
            $result = 0;
            $shift = 0;
            do {
                $b = ord($polyline[$index++]) - 63;
                $result |= ($b & 0x1F) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $delta = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $lat += $delta;

            $result = 0;
            $shift = 0;
            do {
                $b = ord($polyline[$index++]) - 63;
                $result |= ($b & 0x1F) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $delta = ($result & 1) ? ~($result >> 1) : ($result >> 1);
            $lng += $delta;

            // Return as [lon, lat]
            $points[] = [$lng * 1e-5, $lat * 1e-5];
        }

        return $points;
    }

    /**
     * Convert encoded polyline to MultiLineString GeoJSON string.
     */
    public static function polylineToMultiline(string $polyline): string
    {
        $coordinates = self::decodePolyline($polyline);

        return json_encode([
            'type' => 'MultiLineString',
            'coordinates' => [$coordinates],
        ]);
    }

    /**
     * Flatten multiline GeoJSON string to an array of points.
     * GeoJSON coordinates come as [lon, lat].
     */
    public static function multilineToPoints(string $geojson): array
    {
        $data = json_decode($geojson, true);
        if (! is_array($data) || ! isset($data['type'])) {
            return [];
        }
        $lines = [];
        if ($data['type'] === 'LineString') {
            $lines[] = $data['coordinates'];
        } elseif ($data['type'] === 'MultiLineString') {
            $lines = $data['coordinates'];
        } else {
            return [];
        }
        $result = [];
        foreach ($lines as $line) {
            foreach ($line as $coord) {
                $result[] = ['lat' => $coord[1], 'lng' => $coord[0]];
            }
        }

        return $result;
    }

    /**
     * Convert multiline GeoJSON string to an array of coordinate lines.
     * GeoJSON coordinates come as [lon, lat].
     */
    public static function multilineToCoordlines(string $geojson): array
    {
        $data = json_decode($geojson, true);
        if (! is_array($data) || ! isset($data['type'])) {
            return [];
        }
        $lines = [];
        if ($data['type'] === 'LineString') {
            $lines[] = $data['coordinates'];
        } elseif ($data['type'] === 'MultiLineString') {
            $lines = $data['coordinates'];
        } else {
            return [];
        }

        return array_map(function ($line) {
            return array_map(function ($coord) {
                return ['lat' => $coord[1], 'lng' => $coord[0]];
            }, $line);
        }, $lines);
    }

    /**
     * Swap latitude and longitude in LineString or MultiLineString GeoJSON.
     */
    public static function oldformatToMultiline(string $geojson): string
    {
        $data = json_decode($geojson, true);
        if (! is_array($data) || ! isset($data['type'])) {
            return $geojson;
        }
        $lines = [];
        if ($data['type'] === 'LineString') {
            $lines[] = $data['coordinates'];
        } elseif ($data['type'] === 'MultiLineString') {
            $lines = $data['coordinates'];
        } else {
            return $geojson;
        }
        $lines = array_map(function ($line) {
            return array_map('array_reverse', $line);
        }, $lines);

        return json_encode([
            'type' => 'MultiLineString',
            'coordinates' => $lines,
        ]);
    }

    /**
     * Convert a MultiLineString GeoJSON to the old format.
     * This is used for compatibility with older data formats.
     *
     * @return string WKB representation of the MultiLineString
     */
    public static function MultilineToOldfomat(string $geojson): string
    {
        $data = json_decode($geojson, true);
        if (! is_array($data) || ! isset($data['type'])) {
            return $geojson;
        }
        $lines = [];
        if ($data['type'] === 'LineString') {
            $lines[] = $data['coordinates'];
        } elseif ($data['type'] === 'MultiLineString') {
            $lines = $data['coordinates'];
        } else {
            return $geojson;
        }
        $lines = array_map(function ($line) {
            return array_map('array_reverse', $line);
        }, $lines);
        $g = resolve('geometry');

        return $g->parseGeoJson(json_encode([
            'type' => 'MultiLineString',
            'coordinates' => $lines,
        ]))->toWkb();

    }

    /**
     * Convert a GeoJSON string directly to WKB.
     */
    public static function geojsonToWkb(string $geojson): string
    {
        $geometry = resolve('geometry');

        return $geometry->parseGeoJson($geojson)->toWkb();
    }
}
