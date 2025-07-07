<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use App\Models\RoleUser;
use App\Models\RoleFeature;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Database\Seeders\FeatureSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $uuid = Str::uuid();
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password'=> bcrypt('123'),
        ]);
        $role =  Role::create([
            'name'=> 'superadmin',
        ]);
        RoleUser::create(
            [
                'role_id'=> $role->id,
                'user_id'=> 1,
            ]
        );
        Feature::create([
            'name'=> '*',
            'description'=> 'mengakses semua fitur superadmin',
        ]);
        RoleFeature::create([
            'role_id'=> $role->id,
            'feature_id'=> 1,
        ]);
        $this->call(SubscriptionSeeder::class);
        $this->call(FeatureSeeder::class);
        
    }
}
