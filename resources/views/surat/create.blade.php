@extends('layouts.cabor')
@section('title', 'Buat Surat')
@section('heading', 'Buat Surat Baru')
@section('content')
<div class="mx-auto max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
    <div class="mb-6">
        <h2 class="text-xl font-black">Form Pengajuan Surat</h2>
        <p class="mt-2 text-sm leading-relaxed text-gray-500">Surat akan tersimpan sebagai draft. Setelah diajukan, Staff KONI akan memeriksa dan meneruskannya kepada Admin KONI jika sudah lengkap.</p>
    </div>
    <form method="POST" action="{{ route('cabor.surat.store') }}" enctype="multipart/form-data">
        @include('surat._form')
    </form>
</div>
@endsection
