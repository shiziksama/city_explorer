<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class MapRendererController extends Controller
{
    public function computeOutCode(
        Point $point,
        float $lat_from,
        float $lat_to,
        float $lng_from,
        float $lng_to,
    ): int {
        $result = 0;
        if ($point->latitude < $lat_from) {
            $result = $result | 1;
        }
        if ($point->latitude > $lat_to) {
            $result = $result | 2;
        }
        if ($point->longitude < $lng_from) {
            $result = $result | 4;
        }
        if ($point->longitude > $lng_to) {
            $result = $result | 8;
        }

        return $result;
    }

    /**
     * @return Collection<int, LineString>
     */
    public function extractVisibleSegments(
        MultiLineString $tracks,
        float $lat_from,
        float $lat_to,
        float $lng_from,
        float $lng_to,
    ): Collection {
        $new_tracks = collect();

        foreach ($tracks->getGeometries() as $track) {
            $track = $track->getGeometries()->values();
            $points_numbers = [];
            foreach ($track as $k => $point) {
                $points_numbers[$k] = $this->computeOutCode($point, $lat_from, $lat_to, $lng_from, $lng_to);
            }
            // var_dump($point)
            $new_track = collect([]);
            // var_dump($points_numbers);
            foreach ($points_numbers as $k => $number) {
                if ($k == 0) {
                    continue;
                }

                if (($number & $points_numbers[$k - 1]) == 0 || $number == 0 || $points_numbers[$k - 1] == 0) { // Значит эта линия пересекает.
                    $new_track->push($track[$k - 1]);
                    if ($k == count($points_numbers) - 1) {
                        $new_track->push($track[$k]);
                        $new_tracks->push(new LineString($new_track, $tracks->srid));
                        $new_track = collect([]); // нужно занулить, чтобы он не добавился после списка
                    }

                    continue;
                }
                if (($number & $points_numbers[$k]) !== 0) { // значит не пересекает. Хватит. добавляем предыдущую?
                    if ($new_track->isNotEmpty()) {
                        $new_track->push($track[$k - 1]);
                        $new_tracks->push(new LineString($new_track, $tracks->srid));
                        $new_track = collect([]);
                    }

                    continue;
                }

            }
            if ($new_track->isNotEmpty()) {
                $new_tracks->push(new LineString($new_track, $tracks->srid));
            }
            // var_dump($new_tracks);
            // var_dump($new_tracks);

            // var_dump($points_numbers);
        }

        return $new_tracks;
    }

    public function user_overlay(
        int $uid,
        int $zoom,
        int $x,
        int $y,
    ): Response {
        $user = User::findOrFail($uid);
        $items_count = pow(2, $zoom);

        $lng_deg_per_item = 360 / $items_count;
        $lng_from = -180 + $x * $lng_deg_per_item;
        $lng_to = -180 + ($x + 1) * $lng_deg_per_item;

        $lat_to = rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
        $lat_from = rad2deg(atan(sinh(pi() * (1 - 2 * ($y + 1) / $items_count))));

        $map = new \Imagick;
        $map->newImage(512, 512, new \ImagickPixel('transparent'));
        $map->setImageFormat('png');
        $draw = new \ImagickDraw;
        $draw->setStrokeColor(new \ImagickPixel('rgba(255, 0, 0, 0.8)'));
        if ($zoom >= 14) {
            $draw->setStrokeWidth(20);
        } elseif ($zoom >= 13) {
            $draw->setStrokeWidth(15);
        } elseif ($zoom >= 11) {
            $draw->setStrokeWidth(5);
        } elseif ($zoom >= 7) {
            $draw->setStrokeWidth(4);
        } else {
            $draw->setStrokeWidth(2);
        }

        $draw->setStrokeLineCap(\Imagick::LINECAP_BUTT); // КОнец линии делает квадратным, потому что другой конец все портит
        $draw->setStrokeLineJoin(\Imagick::LINEJOIN_ROUND); // склейку в полилиниях деляем скругленной по фану.
        $draw->setFillColor(new \ImagickPixel('transparent'));

        $tracks = $user->getTracks($lat_from, $lng_from, $lat_to, $lng_to);
        $has_tracks = false;
        foreach ($tracks as $track) {
            $lines = $track->get_tracks();
            $result_tracks = $this->extractVisibleSegments($lines, $lat_from, $lat_to, $lng_from, $lng_to);
            foreach ($result_tracks as $item) {
                $has_tracks = true;
                $line = $item->getGeometries()->map(function (Point $point) use ($lng_from, $lng_to, $items_count, $y): array {
                    $l['y'] = (1 - log(tan(deg2rad($point->latitude)) + 1 / cos(deg2rad($point->latitude))) / pi()) / 2 * $items_count;
                    $l['y'] -= $y;
                    $l['y'] = 512 * $l['y'];
                    $l['x'] = round(($point->longitude - $lng_from) * 512 / ($lng_to - $lng_from));

                    return $l;
                })->all();
                $draw->polyline(array_merge($line, array_reverse($line))); // линия идет в обе стороны, чтобы не было даже возможности нарисовать область внутри
            }
        }

        if ($has_tracks) {
            $map->drawImage($draw);
            $imagefile = $map->getImageBlob();
        } else {
            $imagefile = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAgAAAAIAAQMAAADOtka5AAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAADZJREFUeNrtwQEBAAAAgqD+r26IwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4g6CAAABAfU3XgAAAABJRU5ErkJggg==');
        }

        $file_path = base_path('map_overlay/'.$uid.'/'.$zoom.'/'.$x.'/'.$y.'.png');
        $dirname = pathinfo($file_path, PATHINFO_DIRNAME);
        if (! is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        file_put_contents($file_path, $imagefile);

        return response($imagefile)->header('Content-type', 'image/png');
    }
}
