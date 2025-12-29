<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->role($request->role);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('first_name')->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => ['users' => $users]]);
        }

        return view('users.index', compact('users'));
    }

    public function managers()
    {
        $managers = User::role(['admin', 'manager'])
            ->orderBy('first_name')
            ->get();

        if (request()->expectsJson()) {
            return response()->json(['data' => ['managers' => $managers]]);
        }

        return view('users.managers', compact('managers'));
    }
}
