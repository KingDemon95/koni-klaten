<form method="POST" action="{{ $action }}" class="space-y-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    @if(!$item && $routePrefix === 'admin')
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold">Kepemilikan
                <select name="scope" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="koni" @selected(old('scope', 'koni') === 'koni')>Inventaris KONI</option><option value="cabor" @selected(old('scope') === 'cabor')>Inventaris Cabang Olahraga</option></select>
            </label>
            <label class="block text-sm font-semibold">Cabang olahraga (jika milik Cabor)
                <select name="cabang_olahraga_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Pilih cabang olahraga</option>@foreach($cabors as $cabor)<option value="{{ $cabor->id }}" @selected(old('cabang_olahraga_id') == $cabor->id)>{{ $cabor->nama }}</option>@endforeach</select>
            </label>
        </div>
    @elseif(!$item)
        <input type="hidden" name="scope" value="cabor">
        <div class="rounded-lg bg-blue-50 p-3 text-sm text-blue-800">Barang ini akan masuk ke inventaris cabang olahraga akun Anda.</div>
    @else
        <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-700">Pemilik: <strong>{{ $item->cabangOlahraga?->nama ?? 'KONI Kabupaten' }}</strong></div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block text-sm font-semibold sm:col-span-2">Nama barang <span class="text-red-600">*</span><input name="nama_barang" value="{{ old('nama_barang', $item?->nama_barang) }}" required maxlength="180" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
        <label class="block text-sm font-semibold">Kategori <span class="text-red-600">*</span><select name="inventory_category_id" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('inventory_category_id', $item?->inventory_category_id) == $category->id)>{{ $category->nama }}{{ $category->cabang_olahraga_id ? ' · '.$category->cabangOlahraga?->nama : ' · Umum' }}</option>@endforeach</select></label>
        <label class="block text-sm font-semibold">Jumlah <span class="text-red-600">*</span><input type="number" name="jumlah" min="1" max="1000000" value="{{ old('jumlah', $item?->jumlah ?? 1) }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
        <label class="block text-sm font-semibold">Satuan <span class="text-red-600">*</span><input name="satuan" value="{{ old('satuan', $item?->satuan ?? 'unit') }}" required maxlength="30" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="unit, buah, set"></label>
        <label class="block text-sm font-semibold">Kondisi <span class="text-red-600">*</span><select name="kondisi" required class="mt-1 w-full rounded-lg border-gray-300 text-sm">@foreach(\App\Models\InventoryItem::CONDITIONS as $key => $label)<option value="{{ $key }}" @selected(old('kondisi', $item?->kondisi ?? 'baik') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label class="block text-sm font-semibold">Lokasi penyimpanan <span class="text-red-600">*</span><input name="lokasi" value="{{ old('lokasi', $item?->lokasi) }}" required maxlength="150" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
        <label class="block text-sm font-semibold">Tanggal perolehan<input type="date" name="tanggal_perolehan" value="{{ old('tanggal_perolehan', $item?->tanggal_perolehan?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
        <label class="block text-sm font-semibold">Harga perolehan (Rp)<input type="number" name="harga_perolehan" min="0" step="0.01" value="{{ old('harga_perolehan', $item?->harga_perolehan) }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
        <label class="block text-sm font-semibold">Status<select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="aktif" @selected(old('status', $item?->status ?? 'aktif') === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $item?->status) === 'nonaktif')>Nonaktif</option></select></label>
        <label class="block text-sm font-semibold sm:col-span-2">Keterangan<textarea name="deskripsi" rows="3" maxlength="3000" class="mt-1 w-full rounded-lg border-gray-300 text-sm">{{ old('deskripsi', $item?->deskripsi) }}</textarea></label>
    </div>

    @if($errors->any()) <div role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">Periksa kembali data yang diisi: {{ $errors->first() }}</div> @endif
    <div class="flex flex-wrap gap-2"><button class="rounded-lg bg-[#D71920] px-5 py-2.5 text-sm font-bold text-white">Simpan</button><a href="{{ route($routePrefix.'.inventaris.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-600">Batal</a></div>
</form>
