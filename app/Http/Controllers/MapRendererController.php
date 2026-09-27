<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Tile;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class MapRendererController extends Controller
{
    private const TILE_SIZE = 512;

    public function computeOutCode(
        Point $point,
        Tile $tile,
    ): int {
        $result = 0;
        if ($point->latitude < $tile->latFrom()) {
            $result = $result | 1;
        }
        if ($point->latitude > $tile->latTo()) {
            $result = $result | 2;
        }
        if ($point->longitude < $tile->lngFrom()) {
            $result = $result | 4;
        }
        if ($point->longitude > $tile->lngTo()) {
            $result = $result | 8;
        }

        return $result;
    }

    /**
     * @return array{x: float, y: float}
     */
    public function projectToTilePixel(
        Point $point,
        Tile $tile,
        int $pixels = self::TILE_SIZE,
    ): array {
        $globalTileY = (1 - log(tan(deg2rad($point->latitude)) + 1 / cos(deg2rad($point->latitude))) / pi()) / 2 * $tile->tilesPerAxis();

        return [
            'y' => ($globalTileY - $tile->y) * $pixels,
            'x' => round(($point->longitude - $tile->lngFrom()) * $pixels / ($tile->lngTo() - $tile->lngFrom())),
        ];
    }

    /**
     * @return Collection<int, LineString>
     */
    public function extractVisibleSegments(
        MultiLineString $tracks,
        Tile $tile,
    ): Collection {
        $new_tracks = collect();

        foreach ($tracks->getGeometries() as $lineString) {
            $points = $lineString->getGeometries()->values();
            $points_numbers = [];
            foreach ($points as $k => $point) {
                $points_numbers[$k] = $this->computeOutCode($point, $tile);
            }
            // var_dump($point)
            $new_track = collect([]);
            // var_dump($points_numbers);
            foreach ($points_numbers as $k => $number) {
                if ($k == 0) {
                    continue;
                }

                if (($number & $points_numbers[$k - 1]) == 0) { // Значит эта линия пересекает.
                    $new_track->push($points[$k - 1]);
                    if ($k == count($points_numbers) - 1) {
                        $new_track->push($points[$k]);
                        $new_tracks->push(new LineString($new_track, $tracks->srid));
                        $new_track = collect([]); // нужно занулить, чтобы он не добавился после списка
                    }

                    continue;
                }
                if ($new_track->isNotEmpty()) {
                    $new_track->push($points[$k - 1]);
                    $new_tracks->push(new LineString($new_track, $tracks->srid));
                    $new_track = collect([]);
                }

            }
            if ($new_track->isNotEmpty()) {
                $new_tracks->push(new LineString($new_track, $tracks->srid));
            }
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
        $tile = new Tile($zoom, $x, $y);

        $map = new \Imagick;
        $map->newImage(self::TILE_SIZE, self::TILE_SIZE, new \ImagickPixel('transparent'));
        $map->setImageFormat('png');
        $draw = new \ImagickDraw;
        $draw->setStrokeColor(new \ImagickPixel('rgba(255, 0, 0, 0.8)'));
        if ($tile->zoom >= 14) {
            $draw->setStrokeWidth(20);
        } elseif ($tile->zoom >= 13) {
            $draw->setStrokeWidth(15);
        } elseif ($tile->zoom >= 11) {
            $draw->setStrokeWidth(5);
        } elseif ($tile->zoom >= 7) {
            $draw->setStrokeWidth(4);
        } else {
            $draw->setStrokeWidth(2);
        }

        $draw->setStrokeLineCap(\Imagick::LINECAP_BUTT); // КОнец линии делает квадратным, потому что другой конец все портит
        $draw->setStrokeLineJoin(\Imagick::LINEJOIN_ROUND); // склейку в полилиниях деляем скругленной по фану.
        $draw->setFillColor(new \ImagickPixel('transparent'));

        $tracks = $user->getTracks($tile);
        $has_tracks = false;
        foreach ($tracks as $track) {
            $multiLines = $track->get_tracks();
            $result_tracks = $this->extractVisibleSegments($multiLines, $tile);
            foreach ($result_tracks as $item) {
                $has_tracks = true;
                $line = $item->getGeometries()
                    ->map(fn (Point $point): array => $this->projectToTilePixel($point, $tile))
                    ->all();
                $draw->polyline(array_merge($line, array_reverse($line))); // линия идет в обе стороны, чтобы не было даже возможности нарисовать область внутри
            }
        }

        if ($has_tracks) {
            $map->drawImage($draw);
            $imagefile = $map->getImageBlob();
        } else {
            $imagefile = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAgAAAAIAAQMAAADOtka5AAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAADZJREFUeNrtwQEBAAAAgqD+r26IwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4g6CAAABAfU3XgAAAABJRU5ErkJggg==');
        }

        $file_path = base_path('map_overlay/'.$uid.'/'.$tile->zoom.'/'.$tile->x.'/'.$tile->y.'.png');
        $dirname = pathinfo($file_path, PATHINFO_DIRNAME);
        if (! is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        file_put_contents($file_path, $imagefile);

        return response($imagefile)->header('Content-type', 'image/png');
    }
}
