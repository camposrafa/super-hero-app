<?php

namespace App\DTO;

use App\Exceptions\InvalidAiResponseException;

final readonly class HeroStats
{
    public function __construct(
        public int $strength,
        public int $intelligence,
        public int $speed,
        public int $power,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['strength', 'intelligence', 'speed', 'power'] as $field) {
            if (! isset($data[$field]) || ! is_numeric($data[$field])) {
                throw InvalidAiResponseException::missingField("stats.{$field}");
            }
        }

        return new self(
            strength: self::normalizeStat($data['strength'], 'stats.strength'),
            intelligence: self::normalizeStat($data['intelligence'], 'stats.intelligence'),
            speed: self::normalizeStat($data['speed'], 'stats.speed'),
            power: self::normalizeStat($data['power'], 'stats.power'),
        );
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'strength' => $this->strength,
            'intelligence' => $this->intelligence,
            'speed' => $this->speed,
            'power' => $this->power,
        ];
    }

    private static function normalizeStat(mixed $value, string $field): int
    {
        $stat = (int) $value;

        if ($stat < 0 || $stat > 100) {
            throw InvalidAiResponseException::invalidField($field);
        }

        return $stat;
    }
}
