@extends($layout)
@section('title', $mode === 'create' ? 'Unggah Prestasi' : 'Edit Prestasi')
@section('heading', 'Prestasi')
@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <a href="{{ route($prefix.'.prestasi.index') }}" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Kembali ke Prestasi Saya</a>
    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        <h2 class="text-xl font-black">{{ $mode === 'create' ? 'Unggah Prestasi Baru' : 'Perbarui Prestasi' }}</h2>
        <p class="mt-1 text-sm text-gray-500">Cabang olahraga: {{ $profile->cabangOlahraga?->nama }}. Bukti prestasi wajib dilampirkan dan akan diperiksa Admin Cabor.</p>
        @if($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ $mode === 'create' ? route($prefix.'.prestasi.store') : route($prefix.'.prestasi.update', $prestasi) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf @if($mode === 'edit') @method('PUT') @endif
            <div><label class="mb-1 block text-sm font-bold" for="nama_kejuaraan">Nama kejuaraan</label><input id="nama_kejuaraan" name="nama_kejuaraan" required maxlength="180" value="{{ old('nama_kejuaraan', $prestasi->nama_kejuaraan) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="mb-1 block text-sm font-bold" for="tingkat">Tingkat kejuaraan</label><select id="tingkat" name="tingkat" required class="w-full rounded-lg border-gray-300 text-sm">@foreach(['lokal' => 'Lokal', 'kabupaten' => 'Kabupaten', 'provinsi' => 'Provinsi', 'nasional' => 'Nasional', 'internasional' => 'Internasional'] as $value => $label)<option value="{{ $value }}" @selected(old('tingkat', $prestasi->tingkat) === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label class="mb-1 block text-sm font-bold" for="tanggal_prestasi">Tanggal kejuaraan</label><input id="tanggal_prestasi" type="date" name="tanggal_prestasi" max="{{ now()->toDateString() }}" required value="{{ old('tanggal_prestasi', $prestasi->tanggal_prestasi?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                <div><label class="mb-1 block text-sm font-bold" for="kategori">Kategori / nomor pertandingan</label><input id="kategori" name="kategori" maxlength="120" value="{{ old('kategori', $prestasi->kategori) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                <div><label class="mb-1 block text-sm font-bold" for="hasil">Hasil / peringkat</label><input id="hasil" name="hasil" required maxlength="120" value="{{ old('hasil', $prestasi->hasil) }}" placeholder="Contoh: Juara 1" class="w-full rounded-lg border-gray-300 text-sm"></div>
                <div>
                    <label class="mb-1 block text-sm font-bold" for="jenis_medali">Jenis medali (jika ada)</label>
                    <select id="jenis_medali" name="jenis_medali" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Tidak diklasifikasikan sebagai medali</option>
                        @foreach(['emas' => 'Emas', 'perak' => 'Perak', 'perunggu' => 'Perunggu'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('jenis_medali', $prestasi->jenis_medali) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Pilih hanya jika hasil kejuaraan memang berupa medali. Data ini digunakan dalam statistik medali KONI.</p>
                </div>
            </div>
            <div><label class="mb-1 block text-sm font-bold" for="penyelenggara">Penyelenggara</label><input id="penyelenggara" name="penyelenggara" maxlength="180" value="{{ old('penyelenggara', $prestasi->penyelenggara) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
            <div><label class="mb-1 block text-sm font-bold" for="keterangan">Keterangan (opsional)</label><textarea id="keterangan" name="keterangan" rows="3" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('keterangan', $prestasi->keterangan) }}</textarea></div>
            <div><label class="mb-1 block text-sm font-bold" for="bukti">Bukti prestasi {{ $mode === 'create' ? '(wajib)' : '(opsional jika tidak diganti)' }}</label><input id="bukti" type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png" @required($mode === 'create') class="w-full rounded-lg border border-gray-300 p-2 text-sm"><p class="mt-1 text-xs text-gray-500">PDF, JPG, atau PNG. Maksimal 5 MB. Dokumen tidak dapat diakses publik.</p>@if($mode === 'edit')<p class="mt-1 text-xs text-gray-500">Bukti saat ini: {{ $prestasi->bukti_original_name }}</p>@endif</div>
            <div class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Prestasi tidak akan muncul di halaman publik sebelum diverifikasi Admin Cabor.</div>
            <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">{{ $mode === 'create' ? 'Kirim untuk Verifikasi' : 'Simpan dan Kirim Ulang' }}</button>
        </form>
    </section>
</div>
@endsection
