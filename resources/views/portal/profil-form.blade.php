@extends($layout)

@section('title', 'Edit Biodata')
@section('heading', 'Edit Biodata')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <a href="{{ route($routePrefix.'.profil') }}" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Kembali ke Profil</a>
        <h2 class="mt-3 text-2xl font-black text-gray-900">Perbarui Biodata {{ ucfirst($jenis) }}</h2>
        <p class="mt-1 text-sm text-gray-500">Perubahan hanya berlaku untuk profil yang terhubung dengan akun Anda. Cabang olahraga dan status keanggotaan dikelola oleh Admin Cabor/KONI.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Periksa kembali isian berikut:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route($routePrefix.'.profil.update') }}" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf
        @method('PUT')
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="nama_lengkap" class="mb-1 block text-sm font-bold">Nama lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" required maxlength="150" value="{{ old('nama_lengkap', $item->nama_lengkap) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label for="jenis_kelamin" class="mb-1 block text-sm font-bold">Jenis kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="L" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $item->jenis_kelamin) === 'P')>Perempuan</option>
                </select>
            </div>
            <div>
                <label for="tempat_lahir" class="mb-1 block text-sm font-bold">Tempat lahir</label>
                <input id="tempat_lahir" name="tempat_lahir" maxlength="100" value="{{ old('tempat_lahir', $item->tempat_lahir) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label for="tanggal_lahir" class="mb-1 block text-sm font-bold">Tanggal lahir</label>
                <input id="tanggal_lahir" type="date" name="tanggal_lahir" max="{{ now()->toDateString() }}" value="{{ old('tanggal_lahir', $item->tanggal_lahir?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label for="no_telepon" class="mb-1 block text-sm font-bold">Nomor telepon</label>
                <input id="no_telepon" name="no_telepon" maxlength="30" value="{{ old('no_telepon', $item->no_telepon) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label for="email" class="mb-1 block text-sm font-bold">Email kontak</label>
                <input id="email" type="email" name="email" maxlength="255" value="{{ old('email', $item->email) }}" class="w-full rounded-lg border-gray-300 text-sm">
                <p class="mt-1 text-xs text-gray-500">Email kontak profil tidak mengubah email yang digunakan untuk login.</p>
            </div>
            <div class="sm:col-span-2">
                <label for="alamat" class="mb-1 block text-sm font-bold">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('alamat', $item->alamat) }}</textarea>
            </div>
            @if ($jenis === 'pelatih')
                <div>
                    <label for="pendidikan_terakhir" class="mb-1 block text-sm font-bold">Pendidikan terakhir</label>
                    <input id="pendidikan_terakhir" name="pendidikan_terakhir" maxlength="150" value="{{ old('pendidikan_terakhir', $item->pendidikan_terakhir) }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="spesialisasi" class="mb-1 block text-sm font-bold">Spesialisasi</label>
                    <input id="spesialisasi" name="spesialisasi" maxlength="150" value="{{ old('spesialisasi', $item->spesialisasi) }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="pengalaman_tahun" class="mb-1 block text-sm font-bold">Pengalaman (tahun)</label>
                    <input id="pengalaman_tahun" type="number" min="0" max="60" name="pengalaman_tahun" value="{{ old('pengalaman_tahun', $item->pengalaman_tahun) }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label for="sertifikasi" class="mb-1 block text-sm font-bold">Sertifikasi</label>
                    <textarea id="sertifikasi" name="sertifikasi" rows="2" maxlength="5000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('sertifikasi', $item->sertifikasi) }}</textarea>
                </div>
            @endif
            <div class="sm:col-span-2">
                <label for="foto" class="mb-1 block text-sm font-bold">Foto profil (opsional)</label>
                <input id="foto" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-lg border border-gray-300 p-2 text-sm">
                <p class="mt-1 text-xs text-gray-500">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                @if ($item->foto_path)
                    <img src="{{ asset('storage/'.$item->foto_path) }}" alt="Foto profil saat ini" class="mt-3 h-20 w-20 rounded-xl object-cover">
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
            <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Simpan Biodata</button>
            <a href="{{ route($routePrefix.'.profil') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700">Batal</a>
        </div>
    </form>
</div>
@endsection
