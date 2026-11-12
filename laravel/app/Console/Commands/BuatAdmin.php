<?php

namespace App\Console\Commands;

use App\Models\BankSampah;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BuatAdmin extends Command
{
    protected $signature = 'admin:buat
        {email : Email untuk login}
        {nama : Nama admin}
        {--bank= : ID bank sampah. Kosongkan untuk super admin (akses semua bank sampah)}';

    protected $description = 'Membuat akun admin baru (kata sandi ditanyakan saat perintah dijalankan)';

    public function handle(): int
    {
        $bankId = $this->option('bank');

        if ($bankId !== null && ! BankSampah::whereKey($bankId)->exists()) {
            $this->error("Bank sampah dengan ID {$bankId} tidak ditemukan.");

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi (minimal 8 karakter)');

        $validator = Validator::make(
            ['email' => $this->argument('email'), 'password' => $password],
            ['email' => ['required', 'email', 'unique:users,email'], 'password' => ['required', 'min:8']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->forceFill([
            'name' => $this->argument('nama'),
            'email' => $this->argument('email'),
            'password' => Hash::make($password),
            'role' => 'admin',
            'bank_sampah_id' => $bankId,
        ])->save();

        $this->info("Admin dibuat: {$user->email} (" . ($bankId ? "bank sampah ID {$bankId}" : 'super admin') . ').');

        return self::SUCCESS;
    }
}