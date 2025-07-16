<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user = new User();

        $user->username = 'admin';
        $user->password = bcrypt('qweqwe');
        $user->save();

/*
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('qweqwe'),
        ]); 
        
        */
        // User::factory(10)->create();
    }
}
