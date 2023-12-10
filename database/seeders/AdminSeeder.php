<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use App\Helpers\Constant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->truncate();
        $user = User::create([
            'name' => 'super-admin',
            'email' => 'superadmin@rsm.com',
            'country_code' => '92',
            'phone_number' => '0900786015',
            'password' => Hash::make('Admin123!'),
            'status' => Constant::Yes,
            'user_type' => Constant::USER_TYPES['Admin'],
            'login_attempts' => Constant::No,
        ]);

        $role = Role::where('type_id', Constant::USER_TYPES['Admin'])->first();
        $user->assignRole([$role->id]);


        $user = User::create([
            'name' => 'Don Robaldson',
            'email' => 'don@rsm.com',
            'country_code' => '92',
            'phone_number' => '0900786015',
            'password' => Hash::make('Admin123!'),
            'status' => Constant::Yes,
            'user_type' => Constant::USER_TYPES['Super-Admin'],
            'login_attempts' => Constant::No,
        ]);

        $role = Role::where('type_id', Constant::USER_TYPES['Admin'])->first();
        $user->assignRole([$role->id]);
    }
}
