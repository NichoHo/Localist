<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Free', 'price_monthly' => 0, 'max_photos' => 3, 'allows_website' => false, 'priority_rank' => 0],
            ['name' => 'Featured', 'price_monthly' => 149000, 'max_photos' => 10, 'allows_website' => true, 'priority_rank' => 1],
            ['name' => 'Premium', 'price_monthly' => 299000, 'max_photos' => 25, 'allows_website' => true, 'priority_rank' => 2],
        ] as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }

        User::updateOrCreate(
            ['email' => 'admin@localist.test'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );
    }
}
