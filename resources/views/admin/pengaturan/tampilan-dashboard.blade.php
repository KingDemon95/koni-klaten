@extends('layouts.admin')

@section('title', 'Tampilan Dashboard')
@section('heading', 'Tampilan Dashboard')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Personalisasi panel</p>
            <h2 class="mt-2 text-xl font-black">Backdrop Dashboard Admin KONI</h2>
            <p class="mt-1 text-sm text-gray-500">Gambar tampil pada area sambutan dashboard. Sistem menambahkan lapisan gelap agar teks tetap terbaca.</p>
        </div>

        @if($settings->backdrop_path)
            <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                <img src="{{ asset('storage/'.$settings->backdrop_path) }}" alt="Backdrop dashboard saat ini" class="h-56 w-full object-cover" style="object-position: {{ $settings->background_position }}">
                <p class="px-4 py-2 text-xs text-gray-500">Backdrop saat ini</p>
            </div>
        @else
            <div class="mb-6 flex h-44 items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gradient-to-r from-red-800 to-red-600 text-sm font-semibold text-white">Belum ada gambar. Dashboard menggunakan latar bawaan.</div>
        @endif

        <form method="POST" action="{{ route('admin.dashboard-appearance.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="backdrop" class="mb-2 block text-sm font-bold">Unggah gambar backdrop</label>
                <input id="backdrop" type="file" name="backdrop" accept=".jpg,.jpeg,.png,.webp" class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-sm">
                <p class="mt-1 text-xs text-gray-500">Format JPG, PNG, atau WEBP. Maksimal 4 MB.</p>
                @error('backdrop')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="overlay_opacity" class="mb-2 block text-sm font-bold">Kegelapan lapisan <span id="opacity-value">{{ $settings->overlay_opacity }}%</span></label>
                    <input id="overlay_opacity" type="range" name="overlay_opacity" min="15" max="85" value="{{ old('overlay_opacity', $settings->overlay_opacity) }}" class="w-full accent-red-600">
                    <p class="mt-1 text-xs text-gray-500">Nilai lebih tinggi membuat lapisan lebih gelap dan teks lebih kontras.</p>
                    @error('overlay_opacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="background_position" class="mb-2 block text-sm font-bold">Posisi gambar</label>
                    <select id="background_position" name="background_position" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="center" @selected(old('background_position', $settings->background_position) === 'center')>Tengah</option>
                        <option value="top" @selected(old('background_position', $settings->background_position) === 'top')>Atas</option>
                        <option value="bottom" @selected(old('background_position', $settings->background_position) === 'bottom')>Bawah</option>
                    </select>
                    @error('background_position')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($settings->backdrop_path)
                <label class="flex items-center gap-3 rounded-lg border border-red-100 bg-red-50 p-4 text-sm font-semibold text-red-800">
                    <input type="hidden" name="remove_backdrop" value="0">
                    <input type="checkbox" name="remove_backdrop" value="1" class="rounded border-red-300 text-red-600">
                    Hapus backdrop saat ini dan gunakan latar bawaan
                </label>
            @endif

            <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
                <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Simpan Tampilan</button>
                <a href="{{ route('dashboard.admin-koni') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700">Lihat Dashboard</a>
            </div>
        </form>
    </section>
</div>

<script>
    (() => {
        const slider = document.getElementById('overlay_opacity');
        const output = document.getElementById('opacity-value');
        slider.addEventListener('input', () => output.textContent = `${slider.value}%`);
    })();
</script>
@endsection
