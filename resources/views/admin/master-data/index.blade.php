@extends('layouts.admin')

@section('title', 'Master Data Dinamis')
@section('heading', 'Master Data')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Ada pengaturan yang belum valid.</p>
            <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Konfigurasi formulir</p>
            <h2 class="mt-2 text-xl font-black">Tambah Field Dinamis</h2>
            <p class="mt-1 text-sm text-gray-500">Tambahkan informasi khusus untuk formulir atlet atau pelatih tanpa mengubah tabel utama.</p>
        </div>

        <form method="POST" action="{{ route('admin.master-data.store') }}" class="grid gap-5 md:grid-cols-2">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-bold">Diterapkan pada</label>
                <select name="entity_type" required class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="atlet" @selected(old('entity_type') === 'atlet')>Atlet</option>
                    <option value="pelatih" @selected(old('entity_type') === 'pelatih')>Pelatih</option>
                </select>
                @error('entity_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold">Nama field</label>
                <input name="label" required maxlength="120" value="{{ old('label') }}" placeholder="Contoh: Nomor Induk Atlet" class="w-full rounded-lg border-gray-300 text-sm">
                @error('label')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold">Jenis input</label>
                <select name="type" id="custom-field-type" required class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach(['text' => 'Teks singkat', 'textarea' => 'Teks panjang', 'number' => 'Angka', 'date' => 'Tanggal', 'email' => 'Email', 'url' => 'URL', 'select' => 'Pilihan dropdown', 'checkbox' => 'Checkbox'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', 'text') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="custom-field-options-wrap">
                <label class="mb-2 block text-sm font-bold">Opsi pilihan</label>
                <textarea name="options_text" rows="3" placeholder="Satu opsi per baris. Wajib untuk dropdown." class="w-full rounded-lg border-gray-300 text-sm">{{ old('options_text') }}</textarea>
                @error('options_text')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold">Urutan tampil</label>
                <input type="number" name="sort_order" min="0" max="65535" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="flex items-center gap-3 pt-7">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" id="field-required" name="is_required" value="1" @checked(old('is_required')) class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                <label for="field-required" class="text-sm font-semibold">Wajib diisi</label>
            </div>
            <div class="md:col-span-2">
                <button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Simpan Field</button>
            </div>
        </form>
    </section>

    <section class="space-y-4">
        <div>
            <h2 class="text-lg font-black">Field yang tersedia</h2>
            <p class="mt-1 text-sm text-gray-500">Field nonaktif tidak muncul pada formulir baru. Nilai yang pernah tersimpan tetap ada.</p>
        </div>

        @forelse($fields as $field)
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <form method="POST" action="{{ route('admin.master-data.update', $field) }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs font-bold text-gray-500">Untuk</label>
                        <p class="rounded-lg bg-gray-50 px-3 py-2.5 text-sm font-bold">{{ $field->entity_type === 'atlet' ? 'Atlet' : 'Pelatih' }}</p>
                        <p class="mt-1 text-[11px] text-gray-400">Kode: {{ $field->field_key }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-gray-500">Label</label>
                        <input name="label" required maxlength="120" value="{{ $field->label }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-gray-500">Jenis input</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach(['text' => 'Teks', 'textarea' => 'Teks panjang', 'number' => 'Angka', 'date' => 'Tanggal', 'email' => 'Email', 'url' => 'URL', 'select' => 'Dropdown', 'checkbox' => 'Checkbox'] as $value => $label)
                                <option value="{{ $value }}" @selected($field->type === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold text-gray-500">Opsi dropdown</label>
                        <textarea name="options_text" rows="2" class="w-full rounded-lg border-gray-300 text-xs" placeholder="Satu opsi per baris">{{ implode("\n", $field->options ?? []) }}</textarea>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="is_required" value="0"><input type="checkbox" name="is_required" value="1" @checked($field->is_required) class="rounded border-gray-300 text-red-600"> Wajib</label>
                        <label class="flex items-center gap-2 text-xs font-semibold"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($field->is_active) class="rounded border-gray-300 text-red-600"> Aktif</label>
                        <input type="number" name="sort_order" min="0" max="65535" value="{{ $field->sort_order }}" aria-label="Urutan tampil" class="w-20 rounded-lg border-gray-300 text-xs">
                        <button class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white">Simpan</button>
                    </div>
                </form>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-3">
                    <span class="text-xs {{ $field->is_active ? 'text-green-700' : 'text-gray-400' }}">{{ $field->is_active ? 'Aktif pada formulir' : 'Nonaktif' }}</span>
                    @if($field->is_active)
                        <form method="POST" action="{{ route('admin.master-data.destroy', $field) }}" onsubmit="return confirm('Nonaktifkan field ini? Nilai lama tidak akan dihapus.');">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs font-bold text-red-600 hover:underline">Nonaktifkan field</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">Belum ada field tambahan. Gunakan formulir di atas untuk menambah field pertama.</div>
        @endforelse
    </section>
</div>

<script>
    (() => {
        const type = document.getElementById('custom-field-type');
        const options = document.getElementById('custom-field-options-wrap');
        const refresh = () => options.classList.toggle('hidden', type.value !== 'select');
        type.addEventListener('change', refresh);
        refresh();
    })();
</script>
@endsection
