@extends('layouts.app')

@section('title', 'Administrator sign in')

@section('content')
    <section class="card narrow">
        <h1>Administrator sign in</h1>
        <p>Sign in with your email address or username.</p>
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="field">
                <label for="login">Email or username</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <label class="checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button type="submit">Sign in</button>
        </form>
    </section>
@endsection
