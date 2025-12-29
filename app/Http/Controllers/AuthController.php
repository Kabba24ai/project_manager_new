<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:admin,manager,developer,designer',
        ]);

        // Split name into first_name and last_name
        $nameParts = explode(' ', trim($request->name), 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

        // Generate unique_id and employee_code
        $count = User::count() + 1;
        $uniqueId = 'EMP' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $employeeCode = 'E' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $user = User::create([
            'unique_id' => $uniqueId,
            'employee_code' => $employeeCode,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'Active',
            'start_date' => now(),
        ]);

        // Assign role using Spatie Permission
        $roleName = $request->role ?? 'developer';
        $user->assignRole($roleName);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function getCurrentUser()
    {
        if (Auth::check()) {
            return response()->json(Auth::user());
        }

        return response()->json(null, 401);
    }
}
