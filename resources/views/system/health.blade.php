@extends('layouts.admin')

@section('title', 'Kesehatan Sistem')
@section('heading', 'Kesehatan Sistem')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#D71920]">OPERASIONAL APLIKASI</p>
                <h2 class="mt-2 text-xl font-black text-gray-900">Monitoring kesehatan sistem</h2>
                <p class="mt-1 text-sm text-gray-500">Pemeriksaan koneksi layanan, penyimpanan, backup, dan scheduler.</p>
            </div>
            @php
                $overallClasses = match ($report['overall']) {
                    'ok' => 'bg-green-50 text-green-700',
                    'warning' => 'bg-amber-50 text-amber-700',
                    default => 'bg-red-50 text-red-700',
                };
            @endphp
            <span class="rounded-full px-3 py-2 text-xs font-bold {{ $overallClasses }}">{{ $report['overall_label'] }}</span>
        </div>
        <p class="mt-4 text-xs text-gray-500">Diperiksa: {{ $report['checked_at']->format('d/m/Y H:i:s') }}</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2">
        @foreach ($report['checks'] as $check)
            @php
                $statusClasses = match ($check['status']) {
                    'ok' => 'bg-green-50 text-green-700',
                    'warning' => 'bg-amber-50 text-amber-700',
                    'not_configured' => 'bg-gray-100 text-gray-600',
                    default => 'bg-red-50 text-red-700',
                };
                $statusLabel = match ($check['status']) {
                    'ok' => 'Normal',
                    'warning' => 'Perlu diperiksa',
                    'not_configured' => 'Belum dikonfigurasi',
                    default => 'Gagal',
                };
            @endphp
            <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-bold text-gray-900">{{ $check['label'] }}</h3>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClasses }}">{{ $statusLabel }}</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-gray-600">{{ $check['detail'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-xl border border-blue-200 bg-blue-50 p-5">
        <h3 class="font-bold text-blue-900">Catatan keamanan</h3>
        <p class="mt-2 text-sm leading-6 text-blue-800">Halaman ini tidak menampilkan kredensial, alamat server, APP_KEY, atau isi file backup. Sinkronisasi off-site hanya aktif setelah Admin mengonfigurasi disk penyimpanan privat.</p>
    </section>
</div>
@endsection
