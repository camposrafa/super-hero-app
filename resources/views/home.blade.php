@extends('layouts.app')

@section('title', 'Anomalias ETEC Lab')

@section('content')
    <section class="home-hero" aria-labelledby="home-title">
        <div class="home-hero-content">
            <p class="eyebrow">Laboratório de personagens</p>
            <h1 id="home-title">ANOMALIAS ETEC LAB</h1>
            <p class="hero-description">
                Explore anomalias e crie sua própria anomalia utilizando inteligência artificial.
            </p>

            <div class="hero-actions">
                <button class="button button-secondary" type="button" data-coming-soon>
                    Explorar anomalias
                </button>
                <a class="button button-primary" href="{{ route('hero.create') }}">
                    Crie sua anomalia
                </a>
            </div>
        </div>

        <aside class="hero-sheet-preview" aria-label="Prévia de ficha de personagem">
            <div class="sheet-header">
                <span>Ficha 001</span>
                <strong>IA pronta</strong>
            </div>
            <div class="preview-portrait">
                <span>AEL</span>
            </div>
            <dl class="preview-stats">
                <div>
                    <dt>Força</dt>
                    <dd><span style="width: 82%"></span></dd>
                </div>
                <div>
                    <dt>Inteligência</dt>
                    <dd><span style="width: 94%"></span></dd>
                </div>
                <div>
                    <dt>Poder</dt>
                    <dd><span style="width: 88%"></span></dd>
                </div>
            </dl>
        </aside>
    </section>
@endsection
