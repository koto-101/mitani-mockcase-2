<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザー
        User::create([
            'name' => '一般1',
            'email' => 'user1@example.com',
            'password' => Hash::make('user1234'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => '一般2',
            'email' => 'user2@example.com',
            'password' => Hash::make('user1234'),
            'is_admin' => false,
        ]);

        // 管理者ユーザー
        User::create([
            'name' => '管理者1',
            'email' => 'admin1@example.com',
            'password' => Hash::make('admin1234'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => '管理者2',
            'email' => 'admin2@example.com',
            'password' => Hash::make('admin1234'),
            'is_admin' => true,
        ]);
    }
}
