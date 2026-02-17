@extends('layouts.app')

@section('content')
    <div class="login-container" style="min-height: calc(100vh - 260px);">
        <div class="login-box">
            <h1>🌸 Mai Journey</h1>
            <p class="subtitle">Admin Panel</p>
            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required value="{{ old('username') }}">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button class="btn btn-primary" type="submit" style="width: 100%;">Sign In</button>
            </form>
        </div>
    </div>
@endsection
