
@extends('layouts.admin')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')

<div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">

    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-2xl font-black text-red-600">
        KONI
    </div>

    <p class="mt-6 text-xs font-bold tracking-widest text-red-600">
        MODUL SEDANG DIKEMBANGKAN
    </p>

    <h2 class="mt-3 text-2xl font-black">
        {{ $pageTitle }}
    </h2>

    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-500">
        {{ $pageDescription }}
    </p>

    <div class="mt-8 rounded-xl bg-gray-50 p-5">
        <p class="text-sm font-bold">
            Tahap pengembangan berikutnya
        </p>

        <p class="mt-2 text-sm text-gray-500">
            Modul ini akan dilengkapi dengan database,
            formulir pengelolaan, pencarian, pengeditan,
            dan penghapusan data sesuai hak akses pengguna.
        </p>
    </div>

    <a href="{{ route('dashboard.admin-koni') }}"
       class="mt-7 inline-flex rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">
        Kembali ke Dashboard
    </a>

</div>

@endsection
