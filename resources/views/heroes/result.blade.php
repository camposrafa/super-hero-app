<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $hero->name }} - Anomalias ETEC Lab</title>
</head>
<body>
    <main>
        <a href="{{ route('hero.create') }}">Criar outra anomalia</a>

        <h1>{{ $hero->name }}</h1>
        <h2>{{ $hero->alias }}</h2>

        <p>{{ $hero->origin }}</p>
        <p>{{ $hero->personality }}</p>

        <h3>Poderes</h3>
        <ul>
            @foreach ($hero->powers as $power)
                <li>{{ $power }}</li>
            @endforeach
        </ul>

        <h3>Fraqueza</h3>
        <p>{{ $hero->weakness }}</p>

        <blockquote>{{ $hero->quote }}</blockquote>

        <h3>Atributos</h3>
        <dl>
            <dt>Forca</dt>
            <dd>{{ $hero->stats->strength }}</dd>
            <dt>Inteligencia</dt>
            <dd>{{ $hero->stats->intelligence }}</dd>
            <dt>Velocidade</dt>
            <dd>{{ $hero->stats->speed }}</dd>
            <dt>Poder</dt>
            <dd>{{ $hero->stats->power }}</dd>
        </dl>
    </main>
</body>
</html>
