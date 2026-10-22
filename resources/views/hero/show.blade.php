@extends('layouts.app')

@section('title', $hero->name . ' - Anomalias ETEC Lab')

@section('content')
    @php
        $stats = [
            'Força' => $hero->stats->strength,
            'Inteligência' => $hero->stats->intelligence,
            'Velocidade' => $hero->stats->speed,
            'Poder' => $hero->stats->power,
        ];
    @endphp

    <section class="result-shell">
        <div class="hero-card card-panel">
            <div class="result-topline">
                <a class="back-link" href="{{ route('hero.create') }}">Criar outra anomalia</a>
                <span>Ficha gerada por IA</span>
            </div>

            <header class="result-header">
                <p class="eyebrow">Identidade</p>
                <h1>{{ $hero->name }}</h1>
                <p class="alias">{{ $hero->alias }}</p>
            </header>

            <div class="result-grid">
                <section class="info-panel">
                    <h2>Origem</h2>
                    <p>{{ $hero->origin }}</p>
                </section>

                <section class="info-panel">
                    <h2>Personalidade</h2>
                    <p>{{ $hero->personality }}</p>
                </section>

                <section class="info-panel">
                    <h2>Poderes</h2>
                    <ul class="power-list">
                        @foreach ($hero->powers as $power)
                            <li>{{ $power }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="info-panel">
                    <h2>Fraqueza</h2>
                    <p>{{ $hero->weakness }}</p>
                </section>
            </div>

            <blockquote class="hero-quote">
                “{{ $hero->quote }}”
            </blockquote>

            <section class="stats-card" aria-labelledby="stats-title">
                <h2 id="stats-title">Atributos</h2>
                <dl>
                    @foreach ($stats as $label => $value)
                        <div class="stat-row">
                            <dt>{{ $label }}</dt>
                            <dd>
                                <div class="stat-track" aria-hidden="true">
                                    <span style="width: {{ $value }}%"></span>
                                </div>
                                <strong>{{ $value }}</strong>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>
    </section>
@endsection
