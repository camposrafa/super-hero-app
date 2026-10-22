<?php

namespace App\Commands;

final readonly class CreateHeroCommand
{
    public function __construct(
        public string $name,
        public string $type,
        public string $personality,
        public string $mainPower,
        public int $strength,
        public int $intelligence,
        public int $speed,
        public string $universe,
    ) {}
}
