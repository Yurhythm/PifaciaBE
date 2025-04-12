<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::with('roles')->get();
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:6',
            'role_ids'  => 'array',
            'role_ids.*'=> 'exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($request->filled('role_ids')) {
            $user->roles()->sync($request->role_ids);
        }

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:6',
            'role_ids'  => 'array',
            'role_ids.*'=> 'exists:roles,id',
        ]);

        $user->name  = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        if ($request->filled('role_ids')) {
            $user->roles()->sync($request->role_ids);
        }

        return response()->json($user->load('roles'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->roles()->detach();
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}
