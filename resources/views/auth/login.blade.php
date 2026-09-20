@extends('layouts.base')

@section('title', 'Login')

@section('content')
    <h1>Login</h1>

    @if ($errors->any())
        <ul role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <p>
            <label for="email">Email</label><br>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </p>
        <p>
            <label for="password">Password</label><br>
            <input id="password" name="password" type="password" required autocomplete="current-password">
        </p>
        <p>
            <label for="remember">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                Remember Me
            </label>
        </p>
        <button type="submit">Login</button>
    </form>
@endsection
