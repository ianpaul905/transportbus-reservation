<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TransportBus Gabon')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="site-header">
    <div class="container nav-bar">
        <a class="logo" href="{{ route('home') }}">
            <span class="logo-badge">TB</span>
            <span>TransportBus Gabon</span>
        </a>

        <nav class="nav-links">
            <a href="{{ route('home') }}">Accueil</a>

            @auth
                <a href="{{ route('reservations.mes') }}">Mes réservations</a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Administration</a>
                @endif

                <span class="user-badge">{{ auth()->user()->name ?? auth()->user()->nom }}</span>

                <form method="POST" action="{{ route('logout') }}" class="inline-form">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}">Connexion</a>
                <a class="btn btn-sm btn-jaune" href="{{ route('register') }}">Inscription</a>
            @endauth
        </nav>
    </div>
</header>

<main class="container">
    @foreach (['success' => 'alert-success', 'error' => 'alert-error', 'info' => 'alert-info', 'warning' => 'alert-warning'] as $key => $class)
        @if (session($key))
            <div class="alert {{ $class }}">{{ session($key) }}</div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $message)
                <div>{{ $message }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        Projet pédagogique Licence 2 — I.S.T Libreville — Paiement Mobile Money simulé.
    </div>
</footer>
</body>
</html>
