
@extends('layouts.atlet')

@section('title', 'Profil Saya')
@section('heading', 'Profil Atlet')

@section('content')

@if (session('success'))
    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
@endif

<div class="mb-7">

    <a
        href="{{ route('dashboard.atlet') }}"
        class="text-sm font-semibold text-gray-500 hover:text-red-600"
    >
        ← Kembali ke Dashboard
    </a>

    <h2 class="mt-4 text-xl font-black">
        Profil Pribadi Atlet
    </h2>

    <p class="mt-1 text-sm text-gray-500">
        Informasi atlet yang terhubung dengan akun Anda.
    </p>

</div>


@if(!$atlet)

    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-6">

        <h3 class="font-black text-yellow-900">
            Data Profil Belum Tersedia
        </h3>

        <p class="mt-3 text-sm leading-relaxed text-yellow-800">
            Akun Anda belum terhubung dengan profil atlet.
            Hubungi Administrator KONI untuk pemeriksaan data.
        </p>

    </div>

@else

<div class="mb-5 flex flex-wrap gap-3">
    <a href="{{ route('atlet.profil.edit') }}" class="rounded-lg bg-[#E60012] px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">Edit Biodata</a>
</div>


    <!-- KARTU IDENTITAS -->
    <section class="mb-7 rounded-2xl border border-gray-200 bg-white p-6">

        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

            @if($atlet->foto_path)
                
                <img
                    src="{{ asset('storage/'.$atlet->foto_path) }}"
                    alt="Foto {{ $atlet->nama_lengkap }}"
                    width="112"
                    height="112"
                    style="width:112px;height:112px;max-width:112px;max-height:112px;object-fit:cover;flex-shrink:0;border-radius:16px;"
                    class="shrink-0 border border-gray-200"
                >

            @else

                <div class="flex h-32 w-32 items-center justify-center rounded-2xl bg-red-50 text-4xl font-black text-red-600">
                    {{ strtoupper(mb_substr($atlet->nama_lengkap, 0, 1)) }}
                </div>

            @endif

            <div>

                <p class="text-xs font-bold tracking-widest text-red-600">
                    IDENTITAS ATLET
                </p>

                <h3 class="mt-2 text-2xl font-black">
                    {{ $atlet->nama_lengkap }}
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    {{ $atlet->cabangOlahraga?->nama ?? 'Belum ada cabang olahraga' }}
                </p>

                <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-bold
                    {{ $atlet->status === 'aktif'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-gray-100 text-gray-600' }}">

                    {{ $atlet->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}

                </span>

            </div>

        </div>

    </section>


    <!-- BIODATA -->
    <section class="grid gap-6 lg:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
                Biodata Atlet
            </h3>

            <dl class="space-y-5">

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Nama Lengkap
                    </dt>
                    <dd class="mt-1 text-sm font-bold">
                        {{ $atlet->nama_lengkap }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Jenis Kelamin
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ match($atlet->jenis_kelamin) {
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
                        {{ $atlet->tempat_lahir ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Tanggal Lahir
                    </dt>
                    <dd class="mt-1 text-sm">
                        @if($atlet->tanggal_lahir)
                            {{ \Illuminate\Support\Carbon::parse($atlet->tanggal_lahir)
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
                        {{ $atlet->alamat ?: '-' }}
                    </dd>
                </div>

            </dl>

        </div>


        <!-- KONTAK DAN KEATLETAN -->
        <div class="rounded-xl border border-gray-200 bg-white p-6">

            <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
                Kontak dan Keatletan
            </h3>

            <dl class="space-y-5">

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Cabang Olahraga
                    </dt>
                    <dd class="mt-1 text-sm font-bold">
                        {{ $atlet->cabangOlahraga?->nama ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Status Atlet
                    </dt>
                    <dd class="mt-1 text-sm font-bold">
                        {{ ucfirst($atlet->status ?? '-') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Nomor Telepon
                    </dt>
                    <dd class="mt-1 text-sm">
                        {{ $atlet->no_telepon ?: '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-semibold text-gray-500">
                        Email Profil Atlet
                    </dt>
                    <dd class="mt-1 break-all text-sm">
                        {{ $atlet->email ?: '-' }}
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

            </dl>

        </div>

    </section>


    <!-- INFO -->
    <div class="mt-7 rounded-xl border border-blue-100 bg-blue-50 p-5">

        <h3 class="text-sm font-bold text-blue-800">
            Informasi Perubahan Profil
        </h3>

        <p class="mt-2 text-sm leading-relaxed text-blue-700">
            Halaman profil ini bersifat baca saja.
            Jika terdapat kesalahan nama, cabang olahraga,
            tanggal lahir, atau informasi lainnya,
            hubungi Administrator KONI atau Admin Cabor
            yang berwenang.
        </p>

    </div>

@endif

@endsection
