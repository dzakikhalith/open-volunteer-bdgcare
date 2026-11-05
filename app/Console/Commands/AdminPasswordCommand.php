<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

#[Signature('admin:password
    {--email= : Email akun admin (default: ADMIN_EMAIL)}
    {--name= : Nama akun admin (default: ADMIN_NAME)}
    {--password= : Password baru minimal 8 karakter}')]
#[Description('Buat akun admin baru atau atur ulang password admin yang sudah ada')]
class AdminPasswordCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) ($this->option('email') ?: config('admin.email'));

        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'max:255']],
        );

        if ($validator->fails()) {
            $this->error('Email admin tidak valid: '.$validator->errors()->first('email'));

            return self::FAILURE;
        }

        $admin = User::query()->where('email', $email)->first();

        $name = (string) ($this->option('name') ?: $admin?->name ?: config('admin.name'));

        $password = $this->option('password');

        if (blank($password)) {
            $password = $this->askForPassword();

            if ($password === null) {
                return self::FAILURE;
            }
        }

        if (strlen((string) $password) < 8) {
            $this->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        $attributes = [
            'name' => $name,
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ];

        if ($admin) {
            $admin->update($attributes);
            $this->info("Password admin {$email} berhasil diatur ulang.");
        } else {
            User::query()->create($attributes + ['email' => $email]);
            $this->info("Akun admin {$email} berhasil dibuat.");
        }

        if ($this->option('password') === null) {
            $this->newLine();
            $this->line('Simpan password ini di tempat yang aman. Password tidak ditampilkan lagi.');
        }

        return self::SUCCESS;
    }

    /**
     * Ask for the new password, generating one when the input is left empty.
     * Returns null when the confirmation does not match.
     */
    private function askForPassword(): ?string
    {
        if (! $this->input->isInteractive()) {
            $password = Str::password(16);
            $this->warn('Password yang dibuat otomatis: '.$password);

            return $password;
        }

        $password = (string) $this->secret('Password baru (kosongkan untuk membuat password acak)');

        if (blank($password)) {
            $password = Str::password(16);
            $this->warn('Password yang dibuat otomatis: '.$password);

            return $password;
        }

        if ($this->secret('Ulangi password baru') !== $password) {
            $this->error('Konfirmasi password tidak cocok. Silakan jalankan ulang perintah.');

            return null;
        }

        return $password;
    }
}
