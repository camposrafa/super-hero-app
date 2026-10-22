<?php

namespace App\Services;

use App\Exceptions\AiServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;

class AiService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function generateHero(array $payload): array
    {
        $baseUrl = (string) config('services.ai.base_url');
        $apiKey = (string) config('services.ai.api_key');
        $model = (string) config('services.ai.model');
        $timeout = (int) config('services.ai.timeout', 20);

        try {
            $request = Http::baseUrl($baseUrl)
                ->timeout($timeout)
                ->acceptJson()
                ->asJson();

            if ($apiKey !== '') {
                $request = $request->withHeader('x-goog-api-key', $apiKey);
            }

            $response = $request->post($this->endpoint($model), $this->buildRequestPayload($payload));
        } catch (ConnectionException) {
            throw AiServiceException::unavailable();
        }

        if ($response->failed()) {
            throw AiServiceException::unavailable();
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw AiServiceException::invalidJson();
        }

        $hero = $this->extractHero($data);

        if (! is_array($hero)) {
            throw AiServiceException::invalidJson();
        }

        return $hero;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildRequestPayload(array $payload): array
    {
        return [
            'system_instruction' => [
                'parts' => [
                    ['text' => (string) ($payload['system'] ?? '')],
                ],
            ],
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $this->encodeJson([
                                'user_data' => $payload['user_data'] ?? [],
                                'schema' => $payload['schema'] ?? [],
                            ]),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseFormat' => [
                    'text' => [
                        'mimeType' => 'APPLICATION_JSON',
                        'schema' => $this->heroJsonSchema(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|mixed
     */
    private function extractHero(array $data): mixed
    {
        $legacyHero = data_get($data, 'hero');

        if (is_array($legacyHero)) {
            return $legacyHero;
        }

        if ($this->looksLikeHero($data)) {
            return $data;
        }

        $text = $this->extractOutputText($data);

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        try {
            return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw AiServiceException::invalidJson();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractOutputText(array $data): ?string
    {
        foreach ((array) data_get($data, 'candidates', []) as $candidate) {
            foreach ((array) data_get($candidate, 'content.parts', []) as $part) {
                $text = data_get($part, 'text');

                if (is_string($text)) {
                    return $text;
                }
            }
        }

        return null;
    }

    private function endpoint(string $model): string
    {
        $model = trim($model, '/');

        if (! str_starts_with($model, 'models/')) {
            $model = "models/{$model}";
        }

        return "/{$model}:generateContent";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function looksLikeHero(array $data): bool
    {
        return isset($data['name'], $data['alias'], $data['origin'], $data['personality'], $data['powers'], $data['weakness'], $data['quote'], $data['stats']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function encodeJson(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException) {
            throw AiServiceException::invalidJson();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function heroJsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['name', 'alias', 'origin', 'personality', 'powers', 'weakness', 'quote', 'stats'],
            'properties' => [
                'name' => ['type' => 'string'],
                'alias' => ['type' => 'string'],
                'origin' => ['type' => 'string'],
                'personality' => ['type' => 'string'],
                'powers' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 1,
                ],
                'weakness' => ['type' => 'string'],
                'quote' => ['type' => 'string'],
                'stats' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['strength', 'intelligence', 'speed', 'power'],
                    'properties' => [
                        'strength' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'intelligence' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'speed' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'power' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                    ],
                ],
            ],
        ];
    }
}
