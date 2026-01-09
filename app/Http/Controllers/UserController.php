<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

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
        $managerRoleNames = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];
        $managerRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $managerRoleNames))
            ->get();

        $managers = $managerRoles->isEmpty()
            ? collect()
            : User::role($managerRoles)->orderBy('first_name')->get();

        if (request()->expectsJson()) {
            return response()->json(['data' => ['managers' => $managers]]);
        }

        return view('users.managers', compact('managers'));
    }
}
