<?php

namespace App\Support;

use App\Models\User;
use RuntimeException;

class AccountProvisioner
{
    /**
     * Buat akun login yang terhubung ke profil.
     *
     * Password awal diambil dari config/account_defaults.php.
     * Nilai default hanya ditujukan untuk lingkungan demo.
     */
    public static function create(
        string $name,
        string $email,
        string $role,
        ?int $cabangOlahragaId = null,
        bool $active = true,
    ): User {
        $password = config("account_defaults.passwords.{$role}");

        if (! is_string($password) || mb_strlen($password) < 8) {
            throw new RuntimeException("Password awal untuk role {$role} belum dikonfigurasi dengan benar.");
        }

        $user = User::create([
            'name' => trim($name),
            'email' => mb_strtolower(trim($email)),
            'password' => $password,
            'cabang_olahraga_id' => $role === 'admin_cabor' ? $cabangOlahragaId : null,
            'is_active' => $active,
        ]);

        $user->syncRoles([$role]);

        return $user;
    }
}
