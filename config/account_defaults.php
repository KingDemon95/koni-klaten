<?php

/*
|--------------------------------------------------------------------------
| Password Awal Akun Otomatis (DEMO)
|--------------------------------------------------------------------------
|
| Password berikut dipakai bersama oleh akun dengan role yang sama.
| Ini hanya untuk demo dengan data fiktif. Ganti seluruh nilai ini
| dengan kebijakan password yang aman sebelum aplikasi dipakai produksi.
|
*/

return [
    'passwords' => [
        'admin_cabor' => env('DEFAULT_PASSWORD_ADMIN_CABOR', 'CaborDemo123!'),
        'atlet' => env('DEFAULT_PASSWORD_ATLET', '12345678'),
        'pelatih' => env('DEFAULT_PASSWORD_PELATIH', '87654321'),
        'staff_koni' => env('DEFAULT_PASSWORD_STAFF_KONI', 'StaffDemo123!'),
    ],
];
