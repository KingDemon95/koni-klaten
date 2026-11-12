
@extends('layouts.pelatih')

@section('title', 'Profil Pelatih')
@section('heading', 'Profil Saya')

@section('content')

@if (session('success'))
    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
@endif

<div class="mb-7">

    <a href="{{ route('dashboard.pelatih') }}"
       class="text-sm font-semibold text-gray-500 hover:text-red-600">
        ← Kembali ke Dashboard
    </a>

    <h2 class="mt-4 text-xl font-black">
        Profil Pribadi Pelatih
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Biodata dan informasi kepelatihan yang terhubung dengan akun Anda.
    </p>

</div>


@if(!$pelatih)

    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-6">

        <h3 class="font-black text-yellow-900">
            Profil Pelatih Belum Tersedia
        </h3>

        <p class="mt-3 text-sm leading-relaxed text-yellow-800">
            Akun Anda belum terhubung dengan profil Pelatih.
            Hubungi Administrator KONI untuk pemeriksaan data.
        </p>

    </div>

@else

<div class="mb-5 flex flex-wrap gap-3">
    <a href="{{ route('pelatih.profil.edit') }}" class="rounded-lg bg-[#E60012] px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Edit Biodata</a>
</div>


    <!-- IDENTITAS -->
    <section class="mb-7 rounded-2xl border border-gray-200 bg-white p-6">

        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

            @if($pelatih->foto_path)

                <img
                    src="{{ asset('storage/'.$pelatih->foto_path) }}"
                    alt="Foto {{ $pelatih->nama_lengkap }}"
                    width="112"
                    height="112"
                    style="width:112px;height:112px;max-width:112px;max-height:112px;object-fit:cover;flex-shrink:0;border-radius:16px;"
                    class="shrink-0 border border-gray-200"
                >

            @else

                <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-4xl font-black text-red-600">
                    {{ strtoupper(mb_substr($pelatih->nama_lengkap, 0, 1)) }}
                </div>

            @endif

            <div class="min-w-0">

                <p class="text-xs font-bold tracking-widest text-red-600">
                    IDENTITAS PELATIH
                </p>

                <h3 class="mt-2 text-2xl font-black">
                    {{ $pelatih->nama_lengkap }}
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    {{ $pelatih->cabangOlahraga?->nama ?? 'Belum ada cabor' }}
                </p>

                <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-bold
                    {{ $pelatih->status === 'aktif'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-gray-100 text-gray-600' }}">

                    {{ $pelatih->status === 'aktif'
                        ? 'Pelatih Aktif'
                        : 'Pelatih Nonaktif' }}

                </span>

            </div>

        </div>

    </section>


    <div class="grid gap-6 lg:grid-cols-2">

        <!-- BIODATA -->
        <section class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
                Biodata Pelatih
            </h3>

            <dl class="space-y-5">

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Nama Lengkap
                    </dt>
                    <dd class="mt-1 text-sm font-bold">
                        {{ $pelatih->nama_lengkap }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Jenis Kelamin
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ match($pelatih->jenis_kelamin) {
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                            default => '-',
                        } }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Tempat Lahir
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ $pelatih->tempat_lahir ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Tanggal Lahir
                    </dt>
                    <dd class="mt-1 text-sm">
                        @if($pelatih->tanggal_lahir)
                            {{ \Illuminate\Support\Carbon::parse($pelatih->tanggal_lahir)
                                ->locale('id')
                                ->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Alamat
                    </dt>
                    <dd class="mt-1 whitespace-pre-line text-sm">
                        {{ $pelatih->alamat ?: '-' }}
                    </dd>
                </div>

            </dl>

        </section>


        <!-- KONTAK -->
        <section class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
                Kontak dan Cabang Olahraga
            </h3>

            <dl class="space-y-5">

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Cabang Olahraga
                    </dt>
                    <dd class="mt-1 text-sm font-bold">
                        {{ $pelatih->cabangOlahraga?->nama ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Nomor Telepon
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ $pelatih->no_telepon ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Email Profil Pelatih
                    </dt>
                    <dd class="mt-1 break-all text-sm">
                        {{ $pelatih->email ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Email Akun Login
                    </dt>
                    <dd class="mt-1 break-all text-sm">
                        {{ $user->email }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Status Pelatih
                    </dt>
                    <dd class="mt-1 text-sm font-bold">
                        {{ ucfirst($pelatih->status ?? '-') }}
                    </dd>
                </div>

            </dl>

        </section>

    </div>


    <!-- RIWAYAT KEPELATIHAN -->
    <section class="mt-6 rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
            Informasi Kepelatihan
        </h3>

        <div class="grid gap-6 md:grid-cols-2">

            <div>
                <p class="text-xs font-semibold text-gray-500">
                    Pendidikan Terakhir
                </p>

                <p class="mt-2 text-sm font-medium">
                    {{ $pelatih->pendidikan_terakhir ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500">
                    Spesialisasi
                </p>

                <p class="mt-2 text-sm font-medium">
                    {{ $pelatih->spesialisasi ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500">
                    Pengalaman Kepelatihan
                </p>

                <p class="mt-2 text-sm font-medium">
                    {{ $pelatih->pengalaman_tahun !== null
                        ? $pelatih->pengalaman_tahun.' tahun'
                        : '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500">
                    Sertifikasi
                </p>

                <p class="mt-2 whitespace-pre-line break-words text-sm font-medium">
                    {{ $pelatih->sertifikasi ?: '-' }}
                </p>
            </div>

        </div>

    </section>


    <!-- INFO -->
    <div class="mt-7 rounded-xl border border-blue-100 bg-blue-50 p-5">

        <h3 class="text-sm font-bold text-blue-800">
            Informasi Perubahan Profil
        </h3>

        <p class="mt-2 text-sm leading-relaxed text-blue-700">
            Halaman ini bersifat baca saja.
            Jika terdapat kesalahan pada biodata, spesialisasi,
            pengalaman, atau sertifikasi, silakan hubungi
            Admin Cabor atau Administrator KONI.
        </p>

    </div>

@endif

@endsection
