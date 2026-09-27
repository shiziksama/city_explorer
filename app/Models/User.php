<?php

namespace App\Models;

use App\Support\Tile;
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
    public function getTracks(Tile $tile): Collection
    {
        $bounds = Polygon::fromArray([
            'type' => 'Polygon',
            'coordinates' => [[
                [$tile->lngFrom(), $tile->latFrom()],
                [$tile->lngTo(), $tile->latFrom()],
                [$tile->lngTo(), $tile->latTo()],
                [$tile->lngFrom(), $tile->latTo()],
                [$tile->lngFrom(), $tile->latFrom()],
            ]],
        ], Srid::WGS84);

        return Track::query()
            ->select(['id', 'track_simple_geo'])
            ->where('uid', $this->id)
            ->whereIntersects('track_simple_geo', $bounds)
            ->get();
    }
}
