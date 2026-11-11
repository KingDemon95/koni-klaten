<?php

namespace App\Http\Controllers\Cabor;

use App\Models\Atlet;

class AtletController extends CaborProfileController
{
    protected function modelClass(): string
    {
        return Atlet::class;
    }

    protected function relationName(): string
    {
        return 'atlets';
    }

    protected function jenis(): string
    {
        return 'atlet';
    }
}
