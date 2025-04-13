<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles',
            'permissions' => 'array|exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $request->name
        ]);

        if ($request->permissions) {
            $role->permissions()->attach($request->permissions);
        }

        audit_trail('Role', 'Tambah', 'Tambah data role '.$request->nama);

        return response()->json($role->load('permissions'), 201);
    }

    public function show($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|unique:roles,name,' . $role->id,
            'permissions' => 'array|exists:permissions,id'
        ]);

        $role->update([
            'name' => $request->name ?? $role->name
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        audit_trail('Role', 'Update', 'Update data role '.$request->nama);

        return response()->json($role->load('permissions'));
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->permissions()->detach();

        audit_trail('Role', 'Hapus', 'Hapus data role '.$role->nama);

        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }

    public function getPermissions($name)
    {
        $role = Role::where('name', $name)->first();

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $permissions = $role->permissions->pluck('name');

        return response()->json([
            'role' => $role->name,
            'permissions' => $permissions
        ]);
    }

    public function getPermissionsList()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }
}
