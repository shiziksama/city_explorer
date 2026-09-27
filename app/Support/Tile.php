<?php

namespace App\Support;

final readonly class Tile
{
    public function __construct(
        public int $zoom,
        public int $x,
        public int $y,
    ) {}

    public function tilesPerAxis(): int
    {
        return 2 ** $this->zoom;
    }

    public function lngFrom(): float
    {
        return -180 + $this->x * $this->longitudeSpan();
    }

    public function lngTo(): float
    {
        return -180 + ($this->x + 1) * $this->longitudeSpan();
    }

    public function latFrom(): float
    {
        return $this->latitudeAt($this->y + 1);
    }

    public function latTo(): float
    {
        return $this->latitudeAt($this->y);
    }

    private function longitudeSpan(): float
    {
        return 360 / $this->tilesPerAxis();
    }

    private function latitudeAt(int $y): float
    {
        return rad2deg(
            atan(sinh(pi() * (1 - 2 * $y / $this->tilesPerAxis())))
        );
    }
}
