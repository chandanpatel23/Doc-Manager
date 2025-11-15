<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    public function run()
    {
        $email = 'admin@example.com';
        $username = 'admin';
        $password = 'admin123';

        $user = User::where('email', $email)->orWhere('username', $username)->first();
        if ($user) {
            $user->update([
                'name' => 'Administrator',
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);
        } else {
            User::create([
                'name' => 'Administrator',
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);
        }
    }
}
