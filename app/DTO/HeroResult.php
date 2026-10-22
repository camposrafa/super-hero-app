<?php

namespace App\DTO;

use App\Exceptions\InvalidAiResponseException;

final readonly class HeroResult
{
    /**
     * @param  list<string>  $powers
     */
    public function __construct(
        public string $name,
        public string $alias,
        public string $origin,
        public string $personality,
        public array $powers,
        public string $weakness,
        public string $quote,
        public HeroStats $stats,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['name', 'alias', 'origin', 'personality', 'weakness', 'quote'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field]) || trim($data[$field]) === '') {
                throw InvalidAiResponseException::missingField($field);
            }
        }

        if (! isset($data['powers']) || ! is_array($data['powers'])) {
            throw InvalidAiResponseException::missingField('powers');
        }

        $powers = array_values(array_filter($data['powers'], fn (mixed $power): bool => is_string($power) && trim($power) !== ''));

        if ($powers === []) {
            throw InvalidAiResponseException::invalidField('powers');
        }

        if (! isset($data['stats']) || ! is_array($data['stats'])) {
            throw InvalidAiResponseException::missingField('stats');
        }

        return new self(
            name: trim($data['name']),
            alias: trim($data['alias']),
            origin: trim($data['origin']),
            personality: trim($data['personality']),
            powers: array_map(fn (string $power): string => trim($power), $powers),
            weakness: trim($data['weakness']),
            quote: trim($data['quote']),
            stats: HeroStats::fromArray($data['stats']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'alias' => $this->alias,
            'origin' => $this->origin,
            'personality' => $this->personality,
            'powers' => $this->powers,
            'weakness' => $this->weakness,
            'quote' => $this->quote,
            'stats' => $this->stats->toArray(),
        ];
    }
}
