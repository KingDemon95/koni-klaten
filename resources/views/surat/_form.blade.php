@csrf
<div class="grid gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="perihal" class="mb-1 block text-sm font-bold text-gray-700">Perihal surat <span class="text-red-600">*</span></label>
        <input id="perihal" name="perihal" required maxlength="200" value="{{ old('perihal', $surat->perihal) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Contoh: Permohonan rekomendasi penggunaan GOR">
        @error('perihal')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="jenis_surat" class="mb-1 block text-sm font-bold text-gray-700">Jenis surat <span class="text-red-600">*</span></label>
        <select id="jenis_surat" name="jenis_surat" required class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            @foreach (['permohonan' => 'Permohonan', 'izin' => 'Izin', 'rekomendasi' => 'Rekomendasi', 'pemberitahuan' => 'Pemberitahuan', 'lainnya' => 'Lainnya'] as $key => $label)
                <option value="{{ $key }}" @selected(old('jenis_surat', $surat->jenis_surat ?: 'permohonan') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('jenis_surat')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="nomor_surat" class="mb-1 block text-sm font-bold text-gray-700">Nomor surat</label>
        <input id="nomor_surat" name="nomor_surat" maxlength="100" value="{{ old('nomor_surat', $surat->nomor_surat) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Opsional">
        @error('nomor_surat')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="tanggal_surat" class="mb-1 block text-sm font-bold text-gray-700">Tanggal surat <span class="text-red-600">*</span></label>
        <input id="tanggal_surat" type="date" name="tanggal_surat" required value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d') ?? now()->toDateString()) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
        @error('tanggal_surat')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label for="ringkasan" class="mb-1 block text-sm font-bold text-gray-700">Ringkasan / keterangan</label>
        <textarea id="ringkasan" name="ringkasan" rows="4" maxlength="5000" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Jelaskan tujuan dan isi singkat surat">{{ old('ringkasan', $surat->ringkasan) }}</textarea>
        @error('ringkasan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label for="file" class="mb-1 block text-sm font-bold text-gray-700">Dokumen surat {{ $surat->exists ? '(opsional jika tidak diganti)' : '*' }}</label>
        @if ($surat->exists && $surat->file_original_name)
            <p class="mb-2 text-xs text-gray-500">File saat ini: {{ $surat->file_original_name }}</p>
        @endif
        <input id="file" type="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="w-full rounded-lg border border-gray-300 bg-white p-2 text-sm" {{ $surat->exists ? '' : 'required' }}>
        <p class="mt-1 text-xs text-gray-500">Format PDF, DOC, DOCX, JPG, atau PNG. Maksimal 10 MB.</p>
        @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<div class="mt-7 flex flex-wrap gap-3">
    <button type="submit" class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Simpan Draft</button>
    <a href="{{ route('cabor.surat.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-bold text-gray-600">Batal</a>
</div>
