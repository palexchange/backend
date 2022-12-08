<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $actions = ['create', 'update', 'delete'];
        $models = [
            'account',
            'city',
            'country',
            'currency',
            'exchange',
            'party',
            'setting',
            'stock',
            'user_account',
            'user',
            'entry',
            'entry_transaction',
            'transfer'
        ];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                Permission::create(['guard_name' => 'api', 'name' => $action . '_' . $model]);
            }
        }
        $permissions = Permission::all();
        $role1 = Role::create(['guard_name' => 'api', 'name' => 'super_admin']);
        $role2 = Role::create(['guard_name' => 'api', 'name' => 'admin']);
        $role3 = Role::create(['guard_name' => 'api', 'name' => 'user']);
        $role2->syncPermissions($permissions);
        $role3->syncPermissions($permissions);

        $user = User::create([
            'name' => 'super admin',
            'email' => 'admin@admin.com', //$this->faker->unique()->safeEmail(), //$this->faker->unique()->safeEmail(),
            'password' => 123456, // 123456
            'remember_token' => Str::random(10),
            'role' => 1,
        ]);
        $user->assignRole($role1);
        $user2 = User::create(['email' => 'k@k.com', 'password' => 123456, 'name' => 'كاظم', 'role' => 2]);
        $user3 = User::create(['email' => 'ah@ah.com', 'password' => 123456, 'name' => 'احمد', 'role' => 2]);
        $user4 = User::create(['email' => 'm@m.com', 'password' => 123456, 'name' => 'محمد', 'role' => 2]);
        $user2->assignRole($role3);
        $user3->assignRole($role3);
        $user4->assignRole($role3);
    }
}
