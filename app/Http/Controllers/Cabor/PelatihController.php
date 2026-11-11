<?php

namespace App\Http\Controllers\Cabor;

use App\Models\Pelatih;

class PelatihController extends CaborProfileController
{
    protected function modelClass(): string
    {
        return Pelatih::class;
    }

    protected function relationName(): string
    {
        return 'pelatihs';
    }

    protected function jenis(): string
    {
        return 'pelatih';
    }

    protected function extraRules(): array
    {
        return [
            'pendidikan_terakhir' => [
                'nullable', 'string', 'max:150',
            ],
            'spesialisasi' => [
                'nullable', 'string', 'max:150',
            ],
            'sertifikasi' => [
                'nullable', 'string', 'max:5000',
            ],
            'pengalaman_tahun' => [
                'nullable', 'integer', 'min:0', 'max:60',
            ],
        ];
    }
}
