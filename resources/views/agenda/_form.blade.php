@php
    $isEdit = $agenda->exists;
    $formAction = $isEdit ? route($routePrefix.'.agenda.update', $agenda) : route($routePrefix.'.agenda.store');
@endphp
<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label for="judul" class="mb-1 block text-sm font-bold text-slate-700">Judul agenda <span class="text-red-600">*</span></label>
        <input id="judul" name="judul" required maxlength="180" value="{{ old('judul', $agenda->judul) }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
        @error('judul')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="jenis" class="mb-1 block text-sm font-bold text-slate-700">Jenis <span class="text-red-600">*</span></label>
            <select id="jenis" name="jenis" required class="w-full rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
                <option value="agenda" @selected(old('jenis', $agenda->jenis ?: 'agenda') === 'agenda')>Agenda</option>
                <option value="kegiatan" @selected(old('jenis', $agenda->jenis) === 'kegiatan')>Kegiatan</option>
                <option value="kejuaraan" @selected(old('jenis', $agenda->jenis) === 'kejuaraan')>Kejuaraan</option>
            </select>
            @error('jenis')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        @if(!$isCaborUser)
            <div>
                <label for="cabang_olahraga_id" class="mb-1 block text-sm font-bold text-slate-700">Cabang olahraga</label>
                <select id="cabang_olahraga_id" name="cabang_olahraga_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Kegiatan tingkat KONI / umum</option>
                    @foreach($cabors as $option)
                        <option value="{{ $option->id }}" @selected((string) old('cabang_olahraga_id', $agenda->cabang_olahraga_id) === (string) $option->id)>{{ $option->nama }}</option>
                    @endforeach
                </select>
                @error('cabang_olahraga_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        @else
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Cabang olahraga</label>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700">{{ $cabor?->nama }}</div>
            </div>
        @endif
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div><label for="tanggal_mulai" class="mb-1 block text-sm font-bold text-slate-700">Tanggal mulai <span class="text-red-600">*</span></label><input type="date" id="tanggal_mulai" name="tanggal_mulai" required value="{{ old('tanggal_mulai', $agenda->tanggal_mulai?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-sm">@error('tanggal_mulai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="tanggal_selesai" class="mb-1 block text-sm font-bold text-slate-700">Tanggal selesai</label><input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $agenda->tanggal_selesai?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-sm">@error('tanggal_selesai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="waktu_mulai" class="mb-1 block text-sm font-bold text-slate-700">Waktu mulai</label><input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', $agenda->waktu_mulai ? substr($agenda->waktu_mulai, 0, 5) : '') }}" class="w-full rounded-lg border-slate-300 text-sm">@error('waktu_mulai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="waktu_selesai" class="mb-1 block text-sm font-bold text-slate-700">Waktu selesai</label><input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', $agenda->waktu_selesai ? substr($agenda->waktu_selesai, 0, 5) : '') }}" class="w-full rounded-lg border-slate-300 text-sm">@error('waktu_selesai')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
    </div>

    <div><label for="lokasi" class="mb-1 block text-sm font-bold text-slate-700">Lokasi kegiatan</label><input id="lokasi" name="lokasi" maxlength="180" value="{{ old('lokasi', $agenda->lokasi) }}" class="w-full rounded-lg border-slate-300 text-sm">@error('lokasi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>

    <div><label for="deskripsi" class="mb-1 block text-sm font-bold text-slate-700">Deskripsi</label><textarea id="deskripsi" name="deskripsi" rows="5" class="w-full rounded-lg border-slate-300 text-sm">{{ old('deskripsi', $agenda->deskripsi) }}</textarea>@error('deskripsi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>

    <div>
        <label for="poster" class="mb-1 block text-sm font-bold text-slate-700">Poster / gambar kegiatan</label>
        <input type="file" id="poster" name="poster" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-slate-300 p-2 text-sm">
        <p class="mt-1 text-xs text-slate-500">Format JPG, PNG, atau WEBP. Maksimal 3 MB.</p>
        @if($agenda->poster_path)<img src="{{ asset('storage/'.$agenda->poster_path) }}" alt="Poster saat ini" class="mt-3 max-h-48 rounded-xl border border-slate-200">@endif
        @error('poster')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    @if($isEdit && $agenda->catatan_revisi)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-sm font-bold text-amber-900">Catatan revisi dari Staff/Admin</p><p class="mt-1 text-sm text-amber-800">{{ $agenda->catatan_revisi }}</p></div>
    @endif

    <div class="flex flex-col-reverse justify-between gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center">
        <a href="{{ route($routePrefix.'.agenda.index') }}" class="rounded-lg border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-50">Kembali</a>
        <button class="rounded-lg bg-[#D71920] px-6 py-3 text-sm font-bold text-white hover:bg-[#B9151B]">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Draft' }}</button>
    </div>
</form>
