<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'telegram_id',
        'data',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return Collection<int, Track>
     */
    public function getTracks(float $lat_from, float $lng_from, float $lat_to, float $lng_to): Collection
    {
        $bounds = Polygon::fromArray([
            'type' => 'Polygon',
            'coordinates' => [[
                [$lng_from, $lat_from],
                [$lng_to, $lat_from],
                [$lng_to, $lat_to],
                [$lng_from, $lat_to],
                [$lng_from, $lat_from],
            ]],
        ], Srid::WGS84);

        return Track::query()
            ->select(['id', 'track_simple_geo'])
            ->where('uid', $this->id)
            ->whereIntersects('track_simple_geo', $bounds)
            ->get();
    }
}
