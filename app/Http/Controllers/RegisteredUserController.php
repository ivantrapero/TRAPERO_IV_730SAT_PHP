<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'first_name' => ['required'],
            'last_name'  => ['required'],
            'email'      => ['required', 'email', 'unique:users,email'], // Added unique validation
            'password'   => ['required', Password::min(6), 'confirmed'],
        ]);

        // Create the user
        $user = User::create([
            'first_name' => $attributes['first_name'],
            'last_name'  => $attributes['last_name'],
            'email'      => $attributes['email'],
            'password'   => bcrypt($attributes['password']), // Hash the password
        ]);

        // Log the user in
        Auth::login($user);

        // Redirect to jobs page
        return redirect('/jobs');
    }
}
