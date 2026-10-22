<?php

namespace Tests\Feature\Heroes;

use Tests\TestCase;

class CreateHeroRequestTest extends TestCase
{
    public function test_it_rejects_invalid_hero_data(): void
    {
        $response = $this->from(route('hero.create'))->post(route('hero.generate'), [
            'name' => 'N',
            'type' => 'wizard',
            'personality' => '',
            'main_power' => '',
            'strength' => 101,
            'intelligence' => -1,
            'speed' => 'fast',
            'universe' => '',
        ]);

        $response->assertRedirect(route('hero.create'));
        $response->assertSessionHasErrors([
            'name',
            'type',
            'personality',
            'main_power',
            'strength',
            'intelligence',
            'speed',
            'universe',
        ]);
    }
}
