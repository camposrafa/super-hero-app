@extends('layouts.app')

@section('title', 'Criar Anomalia - Anomalias ETEC Lab')

@section('content')
    <section class="page-shell">
        <div class="page-heading">
            <p class="eyebrow">Criação de personagem</p>
            <h1>Crie sua anomalia</h1>
            <p>Defina a base da ficha e envie para a IA gerar um personagem original.</p>
        </div>

        @if ($errors->any())
            <div class="alert-panel" role="alert">
                <strong>Revise os dados e tente novamente.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="hero-form card-panel" method="POST" action="{{ route('hero.generate') }}" data-hero-form>
            @csrf

            <div class="form-grid">
                <div class="field-group">
                    <label for="name">Nome da anomalia</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        autocomplete="off"
                        required
                    >
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field-group">
                    <label for="type">Tipo</label>
                    <select id="type" name="type" required>
                        <option value="hero" @selected(old('type', 'hero') === 'hero')>Herói</option>
                        <option value="villain" @selected(old('type') === 'villain')>Vilão</option>
                        <option value="antihero" @selected(old('type') === 'antihero')>Anti-herói</option>
                    </select>
                    @error('type')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="field-group">
                <label for="personality">Personalidade</label>
                <textarea id="personality" name="personality" rows="5" required>{{ old('personality') }}</textarea>
                @error('personality')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-grid">
                <div class="field-group">
                    <label for="main_power">Poder principal</label>
                    <input
                        id="main_power"
                        name="main_power"
                        type="text"
                        value="{{ old('main_power') }}"
                        autocomplete="off"
                        required
                    >
                    @error('main_power')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field-group">
                    <label for="universe">Universo/estilo</label>
                    <select id="universe" name="universe" required>
                        @foreach (['ETEC', 'Urbano brasileiro', 'Anime', 'Cyberpunk', 'Fantasia', 'Original'] as $universe)
                            <option value="{{ $universe }}" @selected(old('universe', 'ETEC') === $universe)>
                                {{ $universe }}
                            </option>
                        @endforeach
                    </select>
                    @error('universe')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="stats-grid" aria-label="Atributos da anomalia">
                @foreach ([
                    'strength' => 'Força',
                    'intelligence' => 'Inteligência',
                    'speed' => 'Velocidade',
                ] as $field => $label)
                    <div class="range-field">
                        <div class="range-label">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <output for="{{ $field }}" data-range-output="{{ $field }}">
                                {{ old($field, 50) }}
                            </output>
                        </div>
                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="range"
                            min="0"
                            max="100"
                            value="{{ old($field, 50) }}"
                            data-range-input="{{ $field }}"
                            required
                        >
                        @error($field)
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            @error('ai')
                <p class="field-error form-error">{{ $message }}</p>
            @enderror

            <div class="form-actions">
                <a class="button button-secondary" href="{{ route('home') }}">Voltar</a>
                <button class="button button-primary" type="submit" data-submit-label="Criar minha anomalia">
                    Criar minha anomalia
                </button>
            </div>
        </form>
    </section>
@endsection
