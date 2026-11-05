<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * The password is read from the environment. When it is not configured,
     * a random password is generated and printed once so no weak default
     * password ever ends up in the repository.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        $admin = User::query()->where('email', $email)->first();

        if ($admin) {
            if ($password) {
                $admin->update([
                    'name' => config('admin.name'),
                    'password' => $password,
                    'role' => 'admin',
                    'is_active' => true,
                ]);
            }

            return;
        }

        if (! $password) {
            $password = Str::password(16);

            $this->command?->warn('ADMIN_PASSWORD belum diatur. Password admin sementara: '.$password);
        }

        User::query()->create([
            'name' => config('admin.name'),
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
