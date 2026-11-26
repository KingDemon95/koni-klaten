@extends('layouts.admin')

@section('title', 'Jadwal Kerja Staff')
@section('heading', 'Jadwal Kerja Staff')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.absensi.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-red-600">
        ← Kembali ke Absensi
    </a>
    <h2 class="mt-3 text-xl font-black text-gray-800">
        Pengaturan Jadwal Kerja
    </h2>
    <p class="mt-1 text-sm text-gray-500">
        Atur hari kerja serta waktu masuk dan pulang Staff KONI.
    </p>
</div>

@include('attendance.schedules._form', [
    'action' => route('admin.absensi.jadwal-kerja.update'),
    'heading' => 'Jadwal Kerja Staff KONI',
    'description' => 'Pengaturan ini berlaku untuk Staff KONI.',
    'oneSlotPerDay' => true,
])
@endsection
