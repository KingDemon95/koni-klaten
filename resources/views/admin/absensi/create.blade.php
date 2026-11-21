@extends('layouts.admin')

@section('title', 'Buat Sesi Absensi')
@section('heading', 'Buat Sesi Absensi')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><a href="{{ route('admin.absensi.index') }}" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Kembali ke Absensi</a></div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-bold">Periksa kembali formulir:</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.absensi.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf
        <div><h2 class="text-xl font-black">Informasi Kegiatan</h2><p class="mt-1 text-sm text-gray-500">Sistem akan membuat QR Code unik untuk sesi ini.</p></div>
        <div>
            <label for="title" class="mb-2 block text-sm font-bold">Nama kegiatan *</label>
            <input id="title" name="title" required maxlength="160" value="{{ old('title') }}" placeholder="Contoh: Latihan Bersama Atlet KONI Klaten" class="w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label for="description" class="mb-2 block text-sm font-bold">Keterangan</label>
            <textarea id="description" name="description" rows="3" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Informasi tambahan untuk peserta">{{ old('description') }}</textarea>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="cabang_olahraga_id" class="mb-2 block text-sm font-bold">Cabang olahraga</label>
                <select id="cabang_olahraga_id" name="cabang_olahraga_id" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">Semua cabang olahraga</option>
                    @foreach($cabors as $cabor)
                        <option value="{{ $cabor->id }}" @selected((string) old('cabang_olahraga_id') === (string) $cabor->id)>{{ $cabor->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="target_role" class="mb-2 block text-sm font-bold">Peserta</label>
                <select id="target_role" name="target_role" required class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="all" @selected(old('target_role', 'all') === 'all')>Atlet dan pelatih</option>
                    <option value="atlet" @selected(old('target_role') === 'atlet')>Atlet saja</option>
                    <option value="pelatih" @selected(old('target_role') === 'pelatih')>Pelatih saja</option>
                </select>
            </div>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="starts_at" class="mb-2 block text-sm font-bold">Mulai *</label><input id="starts_at" type="datetime-local" name="starts_at" required value="{{ old('starts_at', now()->format('Y-m-d\\TH:i')) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
            <div><label for="ends_at" class="mb-2 block text-sm font-bold">Selesai *</label><input id="ends_at" type="datetime-local" name="ends_at" required value="{{ old('ends_at', now()->addHours(2)->format('Y-m-d\\TH:i')) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
        </div>
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-xs leading-5 text-blue-800">Peserta harus login ke akun Atlet atau Pelatih. Mereka perlu membuka QR Code dari perangkat yang memiliki kamera. Browser mensyaratkan HTTPS untuk akses kamera, kecuali localhost.</div>
        <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
            <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white">Buat Sesi dan QR Code</button>
            <a href="{{ route('admin.absensi.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700">Batal</a>
        </div>
    </form>
</div>
@endsection
