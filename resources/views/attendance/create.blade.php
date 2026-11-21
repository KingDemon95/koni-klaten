@extends($layout)

@section('title', 'Buat Sesi Absensi')
@section('heading', 'Buat Sesi Absensi')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><a href="{{ route($routePrefix.'.absensi.index') }}" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Kembali ke Absensi</a></div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><p class="font-bold">Periksa kembali formulir:</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route($routePrefix.'.absensi.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf
        <div><h2 class="text-xl font-black">Informasi Kegiatan</h2><p class="mt-1 text-sm text-gray-500">QR Code dibuat khusus untuk sesi ini dan berubah setiap hari.</p></div>
        <div>
            <label for="title" class="mb-2 block text-sm font-bold">Nama kegiatan *</label>
            <input id="title" name="title" required maxlength="160" value="{{ old('title') }}" placeholder="Contoh: Rapat Koordinasi KONI Klaten" class="w-full rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label for="description" class="mb-2 block text-sm font-bold">Keterangan</label>
            <textarea id="description" name="description" rows="3" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Informasi tambahan untuk peserta">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="cabang_olahraga_id" class="mb-2 block text-sm font-bold">Cabang olahraga</label>
            @if($isCaborAdmin)
                <input type="hidden" name="cabang_olahraga_id" value="{{ $cabors->first()->id }}">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">{{ $cabors->first()->nama }}</div>
                <p class="mt-1 text-xs text-gray-500">Sesi ini otomatis dibatasi untuk cabang olahraga akun Anda.</p>
            @else
                <select id="cabang_olahraga_id" name="cabang_olahraga_id" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">Semua cabang olahraga / kegiatan KONI</option>
                    @foreach($cabors as $cabor)
                        <option value="{{ $cabor->id }}" @selected((string) old('cabang_olahraga_id') === (string) $cabor->id)>{{ $cabor->nama }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <fieldset class="rounded-xl border border-gray-200 p-4">
            <legend class="px-2 text-sm font-bold">Role peserta yang boleh melakukan absensi *</legend>
            <p class="mb-4 text-xs leading-5 text-gray-500">Pilih satu atau beberapa role. Untuk acara yang hanya dihadiri Staff KONI, pilih Staff KONI saja.</p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($roles as $role)
                    <label class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-3 text-sm font-semibold text-gray-700">
                        <input type="checkbox" name="target_roles[]" value="{{ $role }}" @checked(in_array($role, old('target_roles', $roles), true)) class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        {{ $roleLabels[$role] }}
                    </label>
                @endforeach
            </div>
            @error('target_roles')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            @error('target_roles.*')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </fieldset>

        <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="starts_at" class="mb-2 block text-sm font-bold">Mulai *</label><input id="starts_at" type="datetime-local" name="starts_at" required value="{{ old('starts_at', now()->format('Y-m-d\\TH:i')) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
            <div><label for="ends_at" class="mb-2 block text-sm font-bold">Selesai *</label><input id="ends_at" type="datetime-local" name="ends_at" required value="{{ old('ends_at', now()->addHours(2)->format('Y-m-d\\TH:i')) }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
        </div>

        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-xs leading-5 text-blue-800">Setiap peserta wajib mengambil selfie langsung dari kamera saat check-in dan check-out. QR harian hanya berlaku pada tanggal saat QR dibuat. Browser membutuhkan HTTPS untuk akses kamera, kecuali localhost.</div>

        <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5">
            <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white">Buat Sesi dan QR Code</button>
            <a href="{{ route($routePrefix.'.absensi.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-bold text-gray-700">Batal</a>
        </div>
    </form>
</div>
@endsection
