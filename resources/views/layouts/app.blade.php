<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bookstore Inventory') | Bookstore Inventory</title>
    <link rel="stylesheet" href="{{ asset('css/bookstore.css') }}">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ auth()->check() ? route('books.index') : route('login') }}">Bookstore Inventory</a>
        @auth
            <nav aria-label="Main navigation">
                <a class="{{ request()->routeIs('books.*') ? 'is-active' : '' }}" href="{{ route('books.index') }}">Books</a>
                <a class="{{ request()->routeIs('reports.*') ? 'is-active' : '' }}" href="{{ route('reports.index') }}">Reports</a>
                <form method="POST" action="{{ route('logout') }}" class="inline-form">
                    @csrf
                    <button class="link-button" type="submit">Log out</button>
                </form>
            </nav>
        @endauth
    </header>
    <main class="container">
        @if (session('status'))
            <div class="notice success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice error" role="alert">
                <p>Please correct the following:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
