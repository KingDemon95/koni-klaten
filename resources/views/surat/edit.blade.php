@extends('layouts.cabor')
@section('title', 'Edit Surat')
@section('heading', 'Perbaiki Surat')
@section('content')
<div class="mx-auto max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
    <div class="mb-6">
        <h2 class="text-xl font-black">Perbarui Dokumen Surat</h2>
        <p class="mt-2 text-sm text-gray-500">Setelah disimpan, surat kembali menjadi draft. Ajukan ulang agar diperiksa Staff KONI.</p>
    </div>
    <form method="POST" action="{{ route('cabor.surat.update', $surat) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('surat._form')
    </form>
</div>
@endsection
