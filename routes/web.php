<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/signup', function () {
    return view('auth.signup');
});

Route::post('/login', function () {
    // Dummy authentication - accept any email/password
    $email = request('email');
    $password = request('password');
    
    if ($email && $password) {
        session(['user' => ['email' => $email, 'name' => 'Manager']]);
        return redirect('/dashboard');
    }
    
    return back()->with('error', 'Please fill all fields');
});

Route::get('/dashboard', function () {
    if (!session('user')) {
        return redirect('/login');
    }
    return view('dashboard');
});

Route::get('/logout', function () {
    session()->forget('user');
    return redirect('/');
});
