<?php

namespace Tests\Unit\Heroes;

use App\Commands\CreateHeroCommand;
use App\DTO\HeroResult;
use App\Exceptions\InvalidAiResponseException;
use App\Handlers\CreateHeroHandler;
use App\Services\AiService;
use Closure;
use Tests\TestCase;

class CreateHeroHandlerTest extends TestCase
{
    public function test_it_calls_the_ai_service_and_processes_a_valid_response(): void
    {
        $called = false;
        $service = $this->fakeAiService(function (array $payload) use (&$called): array {
            $called = true;

            $this->assertSame('Neon', $payload['user_data']['name']);
            $this->assertStringContainsString('Portugues do Brasil', $payload['system']);
            $this->assertStringContainsString('Retorne somente JSON valido', $payload['system']);

            return $this->validHeroPayload();
        });

        $result = (new CreateHeroHandler($service))->handle($this->command());

        $this->assertTrue($called);
        $this->assertInstanceOf(HeroResult::class, $result);
        $this->assertSame('Neon', $result->name);
        $this->assertSame(88, $result->stats->power);
    }

    public function test_it_rejects_an_invalid_ai_response(): void
    {
        $service = $this->fakeAiService(fn (): array => [
            'name' => 'Neon',
        ]);

        $this->expectException(InvalidAiResponseException::class);

        (new CreateHeroHandler($service))->handle($this->command());
    }

    private function fakeAiService(Closure $callback): AiService
    {
        return new class($callback) extends AiService
        {
            public function __construct(
                private readonly Closure $callback,
            ) {}

            /**
             * @param  array<string, mixed>  $payload
             * @return array<string, mixed>
             */
            public function generateHero(array $payload): array
            {
                return ($this->callback)($payload);
            }
        };
    }

    private function command(): CreateHeroCommand
    {
        return new CreateHeroCommand(
            name: 'Neon',
            type: 'hero',
            personality: 'Corajoso e curioso',
            mainPower: 'Luz solida',
            strength: 72,
            intelligence: 94,
            speed: 81,
            universe: 'Futurista urbano',
        );
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
