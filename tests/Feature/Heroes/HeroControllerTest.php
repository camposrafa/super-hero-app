<?php

namespace Tests\Feature\Heroes;

use App\Commands\CreateHeroCommand;
use App\DTO\HeroResult;
use App\DTO\HeroStats;
use App\Handlers\CreateHeroHandler;
use Mockery;
use Tests\TestCase;

class HeroControllerTest extends TestCase
{
    public function test_it_accepts_a_valid_create_hero_request(): void
    {
        $hero = new HeroResult(
            name: 'Neon',
            alias: 'O Guardiao Digital',
            origin: 'Criado em uma tempestade de dados.',
            personality: 'Corajoso e curioso.',
            powers: ['Luz solida', 'Pulso digital'],
            weakness: 'Campos magneticos instaveis.',
            quote: 'A luz tambem sabe lutar.',
            stats: new HeroStats(72, 94, 81, 88),
        );

        $handler = Mockery::mock(CreateHeroHandler::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn (CreateHeroCommand $command): bool => $command->name === 'Neon'
                && $command->mainPower === 'Luz solida'))
            ->andReturn($hero);

        $this->app->instance(CreateHeroHandler::class, $handler);

        $response = $this->post(route('hero.generate'), [
            'name' => 'Neon',
            'type' => 'hero',
            'personality' => 'Corajoso e curioso',
            'main_power' => 'Luz solida',
            'strength' => 72,
            'intelligence' => 94,
            'speed' => 81,
            'universe' => 'Futurista urbano',
        ]);

        $response->assertOk();
        $response->assertViewIs('hero.show');
        $response->assertViewHas('hero', $hero);
    }
}
