@extends('layouts.admin')

@section('title', 'Jadwal Latihan')
@section('heading', 'Jadwal Latihan')

@section('content')
<div class="mb-6">
    <a href="{{ route('cabor.absensi.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-red-600">
        ← Kembali ke Absensi
    </a>

    <h2 class="mt-3 text-xl font-black text-gray-800">
        Pengaturan Jadwal Latihan
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Cabang Olahraga: <strong>{{ $cabor->nama }}</strong>
    </p>

    <p class="mt-1 text-sm text-gray-500">
        Atur jumlah sesi latihan dan waktu pelaksanaannya untuk setiap hari.
    </p>
</div>

@include('attendance.schedules._form', [
    'action' => route('cabor.absensi.jadwal-latihan.update'),
    'heading' => 'Jadwal Latihan Cabang Olahraga',
    'description' => 'Setiap hari dapat memiliki beberapa sesi latihan.',
    'oneSlotPerDay' => false,
])
@endsection
