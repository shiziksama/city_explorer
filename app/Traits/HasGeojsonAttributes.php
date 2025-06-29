<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasGeojsonAttributes
{
    protected static function bootHasGeojsonAttributes(): void
    {
        static::saving(function ($model) {
            foreach ($model->geojsonFields as $field => $precision) {
                $value = $model->attributes[$field] ?? null;

                if (is_array($value)) {
                    $value = json_encode($value);
                }

                if (is_string($value)) {
                    $model->attributes[$field] = DB::raw("ST_GeomFromGeoJSON('$value')");
                }
            }
        });

        static::retrieved(function ($model) {
            foreach ($model->geojsonFields as $field => $precision) {
                $geo = DB::table($model->getTable())
                    ->where($model->getKeyName(), $model->getKey())
                    ->selectRaw("ST_AsGeoJSON(`$field`, $precision) as geo")
                    ->value('geo');

                $model->setAttribute($field, $geo ? json_decode($geo, true) : null);
            }
        });
    }
}
