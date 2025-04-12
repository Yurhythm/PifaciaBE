<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('role_permission')->truncate();
        DB::table('roles')->truncate();

        Schema::enableForeignKeyConstraints();

        $roles = [
            ['name' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'User', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('roles')->insert($roles);

        $permissions = DB::table('permissions')->pluck('id');

        $adminRoleId = DB::table('roles')->where('name', 'Administrator')->value('id');

        $adminPermissions = $permissions->map(function ($permissionId) use ($adminRoleId) {
            return [
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        DB::table('role_permission')->insert($adminPermissions->toArray());
    }
}
