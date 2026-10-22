<?php

namespace Tests\Unit\Heroes;

use App\Exceptions\AiServiceException;
use App\Services\AiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    public function test_it_treats_api_errors_without_returning_internal_details(): void
    {
        config()->set('services.ai.base_url', 'https://ai.example.test');
        config()->set('services.ai.api_key', 'secret-key');
        config()->set('services.ai.model', 'gemini-test');

        Http::fake([
            'ai.example.test/models/gemini-test:generateContent' => Http::response(['error' => 'provider failure'], 500),
        ]);

        $this->expectException(AiServiceException::class);
        $this->expectExceptionMessage('temporarily unavailable');

        (new AiService)->generateHero(['system' => 'instructions', 'user_data' => []]);
    }

    public function test_it_treats_connection_failures_as_unavailable(): void
    {
        config()->set('services.ai.base_url', 'https://ai.example.test');
        config()->set('services.ai.api_key', 'secret-key');
        config()->set('services.ai.model', 'gemini-test');

        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->expectException(AiServiceException::class);
        $this->expectExceptionMessage('temporarily unavailable');

        (new AiService)->generateHero(['system' => 'instructions', 'user_data' => []]);
    }

    public function test_it_extracts_a_hero_from_a_gemini_generate_content_response(): void
    {
        config()->set('services.ai.base_url', 'https://generativelanguage.example.test/v1beta');
        config()->set('services.ai.api_key', 'secret-key');
        config()->set('services.ai.model', 'gemini-test');

        Http::fake([
            'generativelanguage.example.test/v1beta/models/gemini-test:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode($this->validHeroPayload()),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $hero = (new AiService)->generateHero([
            'system' => 'instructions',
            'user_data' => ['name' => 'Neon'],
            'schema' => ['name' => 'string'],
        ]);

        $this->assertSame('Neon', $hero['name']);
        $this->assertSame(88, $hero['stats']['power']);

        Http::assertSent(fn ($request): bool => $request->hasHeader('x-goog-api-key', 'secret-key')
            && $request['system_instruction']['parts'][0]['text'] === 'instructions'
            && $request['contents'][0]['parts'][0]['text'] !== ''
            && $request['generationConfig']['responseFormat']['text']['mimeType'] === 'APPLICATION_JSON'
            && $request['generationConfig']['responseFormat']['text']['schema']['type'] === 'object');
    }

    /**
     * @return array<string, mixed>
     */
    private function validHeroPayload(): array
    {
        return [
            'name' => 'Neon',
            'alias' => 'O Guardiao Digital',
            'origin' => 'Criado em uma tempestade de dados.',
            'personality' => 'Corajoso e curioso.',
            'powers' => ['Luz solida', 'Pulso digital', 'Escudo holografico'],
            'weakness' => 'Campos magneticos instaveis.',
            'quote' => 'A luz tambem sabe lutar.',
            'stats' => [
                'strength' => 72,
                'intelligence' => 94,
                'speed' => 81,
                'power' => 88,
            ],
        ];
    }
}
