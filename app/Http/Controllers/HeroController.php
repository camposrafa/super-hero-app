<?php

namespace App\Http\Controllers;

use App\Commands\CreateHeroCommand;
use App\Exceptions\AiServiceException;
use App\Exceptions\InvalidAiResponseException;
use App\Handlers\CreateHeroHandler;
use App\Http\Requests\CreateHeroRequest;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HeroController extends Controller
{
    public function create(): View
    {
        return view('hero.create');
    }

    public function generate(CreateHeroRequest $request, CreateHeroHandler $handler): View|RedirectResponse
    {
        $data = $request->validated();

        $command = new CreateHeroCommand(
            name: $data['name'],
            type: $data['type'],
            personality: $data['personality'],
            mainPower: $data['main_power'],
            strength: (int) $data['strength'],
            intelligence: (int) $data['intelligence'],
            speed: (int) $data['speed'],
            universe: $data['universe'],
        );

        try {
            $hero = $handler->handle($command);
        } catch (Exception $e) {
            // } catch (AiServiceException|InvalidAiResponseException) {
            return back()
                ->withInput()
                ->withErrors([
                    'ai' => $e->getMessage(),
                ]);
        }

        return view('hero.show', [
            'hero' => $hero,
        ]);
    }
}
