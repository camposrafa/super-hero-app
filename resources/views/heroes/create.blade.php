<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anomalias ETEC Lab</title>
</head>
<body>
    <main>
        <h1>Anomalias ETEC Lab</h1>

        @if ($errors->any())
            <div role="alert">
                <p>Revise os dados e tente novamente.</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('hero.generate') }}">
            @csrf

            <label>
                Nome da anomalia
                <input name="name" value="{{ old('name') }}" required>
            </label>

            <label>
                Tipo
                <select name="type" required>
                    <option value="hero" @selected(old('type') === 'hero')>Heroi</option>
                    <option value="villain" @selected(old('type') === 'villain')>Vilao</option>
                    <option value="antihero" @selected(old('type') === 'antihero')>Anti-heroi</option>
                </select>
            </label>

            <label>
                Personalidade
                <textarea name="personality" required>{{ old('personality') }}</textarea>
            </label>

            <label>
                Poder principal
                <input name="main_power" value="{{ old('main_power') }}" required>
            </label>

            <label>
                Forca
                <input type="number" name="strength" min="0" max="100" value="{{ old('strength', 50) }}" required>
            </label>

            <label>
                Inteligencia
                <input type="number" name="intelligence" min="0" max="100" value="{{ old('intelligence', 50) }}" required>
            </label>

            <label>
                Velocidade
                <input type="number" name="speed" min="0" max="100" value="{{ old('speed', 50) }}" required>
            </label>

            <label>
                Universo/estilo
                <input name="universe" value="{{ old('universe') }}" required>
            </label>

            <button type="submit">Gerar anomalia</button>
        </form>
    </main>
</body>
</html>
