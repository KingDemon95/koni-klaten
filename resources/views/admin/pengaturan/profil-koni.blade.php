@extends('layouts.admin')

@section('title', 'Profil & Pengaturan KONI')
@section('heading', 'Profil & Pengaturan KONI')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif

    <section class="rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-6 text-white">
        <p class="text-xs font-bold tracking-[0.2em] text-yellow-200">KONTEN PUBLIK WEBSITE</p>
        <h2 class="mt-2 text-2xl font-black">Kelola identitas dan profil KONI Klaten</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-red-100">Informasi yang disimpan di sini digunakan pada halaman profil dan identitas portal publik.</p>
    </section>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><p class="font-bold">Periksa kembali isian berikut:</p><ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <h3 class="text-lg font-black">Identitas organisasi</h3>
            <p class="mt-1 text-xs text-gray-500">Nama dan slogan yang tampil di portal publik.</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-bold" for="nama_organisasi">Nama organisasi *</label><input id="nama_organisasi" name="nama_organisasi" value="{{ old('nama_organisasi', $profil->nama_organisasi) }}" required maxlength="180" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="nama_singkat">Nama singkat *</label><input id="nama_singkat" name="nama_singkat" value="{{ old('nama_singkat', $profil->nama_singkat) }}" required maxlength="80" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="tagline">Slogan</label><input id="tagline" name="tagline" value="{{ old('tagline', $profil->tagline) }}" maxlength="255" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-bold" for="deskripsi">Deskripsi singkat</label><textarea id="deskripsi" name="deskripsi" rows="3" maxlength="3000" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('deskripsi', $profil->deskripsi) }}</textarea></div>
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-bold" for="logo">Logo KONI</label>@if($profil->logo_path)<div class="mb-3 flex items-center gap-4"><img src="{{ asset('storage/'.$profil->logo_path) }}" alt="Logo KONI saat ini" class="h-20 w-20 rounded-xl border border-gray-200 bg-white object-contain p-2"><label class="flex items-center gap-2 text-xs font-semibold text-red-700"><input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"> Hapus logo saat ini</label></div>@endif<input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="block w-full rounded-xl border border-gray-300 p-2 text-sm"><p class="mt-1 text-xs text-gray-500">Format JPG, PNG, atau WEBP. Maksimal 3 MB.</p></div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <h3 class="text-lg font-black">Profil organisasi</h3>
            <div class="mt-5 grid gap-5">
                <div><label class="mb-1.5 block text-sm font-bold" for="sejarah">Sejarah</label><textarea id="sejarah" name="sejarah" rows="5" maxlength="20000" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('sejarah', $profil->sejarah) }}</textarea></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="visi">Visi</label><textarea id="visi" name="visi" rows="3" maxlength="5000" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('visi', $profil->visi) }}</textarea></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="misi">Misi</label><textarea id="misi" name="misi" rows="4" maxlength="5000" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('misi', $profil->misi) }}</textarea></div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            <h3 class="text-lg font-black">Kontak dan media sosial</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="mb-1.5 block text-sm font-bold" for="alamat">Alamat sekretariat</label><textarea id="alamat" name="alamat" rows="2" maxlength="2000" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('alamat', $profil->alamat) }}</textarea></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="telepon">Nomor telepon</label><input id="telepon" name="telepon" value="{{ old('telepon', $profil->telepon) }}" maxlength="40" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $profil->email) }}" maxlength="255" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="website">Website</label><input id="website" type="url" name="website" value="{{ old('website', $profil->website) }}" placeholder="https://" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="instagram">Instagram</label><input id="instagram" type="url" name="instagram" value="{{ old('instagram', $profil->instagram) }}" placeholder="https://instagram.com/..." class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="facebook">Facebook</label><input id="facebook" type="url" name="facebook" value="{{ old('facebook', $profil->facebook) }}" placeholder="https://facebook.com/..." class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
                <div><label class="mb-1.5 block text-sm font-bold" for="youtube">YouTube</label><input id="youtube" type="url" name="youtube" value="{{ old('youtube', $profil->youtube) }}" placeholder="https://youtube.com/..." class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"></div>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3"><a href="{{ route('dashboard.admin-koni') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50">Kembali ke dashboard</a><button type="submit" class="rounded-xl bg-[#E60012] px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-100 hover:bg-red-700">Simpan Profil KONI</button></div>
    </form>
</div>
@endsection
