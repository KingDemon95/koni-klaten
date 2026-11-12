@extends($layout)

@section('title', 'Template Surat')
@section('heading', 'Template Surat')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Periksa kembali data template:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#3b1015] p-6 text-white shadow-sm">
        <p class="text-xs font-bold tracking-[0.2em] text-yellow-300">DOKUMEN ADMINISTRASI KONI KLATEN</p>
        <h2 class="mt-2 text-2xl font-black">Pusat Template Surat</h2>
        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-200">Gunakan template resmi untuk menjaga keseragaman format surat antar cabang olahraga dan pengurus KONI.</p>
    </section>

    @if ($isManager)
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <h3 class="text-lg font-black text-gray-900">Unggah Template Baru</h3>
            <form method="POST" action="{{ route($routePrefix.'.templates.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label for="judul" class="mb-1 block text-sm font-bold text-gray-700">Nama template</label>
                    <input id="judul" name="judul" required maxlength="180" value="{{ old('judul') }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Contoh: Format Surat Permohonan">
                    @error('judul')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="file" class="mb-1 block text-sm font-bold text-gray-700">File template</label>
                    <input id="file" type="file" name="file" required accept=".pdf,.doc,.docx" class="w-full rounded-lg border border-gray-300 p-2 text-sm">
                    <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX · Maksimal 10 MB.</p>
                    @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="deskripsi" class="mb-1 block text-sm font-bold text-gray-700">Keterangan</label>
                    <textarea id="deskripsi" name="deskripsi" rows="2" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Keterangan penggunaan template (opsional)">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2"><button class="rounded-lg bg-[#E60012] px-5 py-3 text-sm font-bold text-white hover:bg-red-700">Unggah Template</button></div>
            </form>
        </section>
    @else
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">Berikut template aktif yang dapat diunduh oleh Atlet, Pelatih, dan Admin Cabor.</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-black text-gray-900">Daftar Template</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $items->total() }} template tersedia.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            @forelse ($items as $item)
                <article class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="font-black text-gray-900">{{ $item->judul }}</h4>
                            <p class="mt-1 text-xs text-gray-500">{{ $item->file_original_name }}</p>
                        </div>
                        @if ($isManager)
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        @endif
                    </div>
                    @if ($item->deskripsi)<p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $item->deskripsi }}</p>@endif
                    <p class="mt-3 text-xs text-gray-400">Diunggah oleh {{ $item->uploader?->name ?? 'Pengelola' }} · {{ $item->created_at?->format('d M Y') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route($routePrefix.'.templates.download', $item) }}" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white hover:bg-gray-700">Unduh Template</a>
                    </div>
                    @if ($isManager)
                        <details class="mt-4 border-t border-gray-100 pt-3">
                            <summary class="cursor-pointer text-xs font-bold text-red-700">Edit pengaturan template</summary>
                            <form method="POST" action="{{ route($routePrefix.'.templates.update', $item) }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                                @csrf @method('PUT')
                                <div><label class="mb-1 block text-xs font-bold text-gray-700" for="judul-{{ $item->id }}">Nama template</label><input id="judul-{{ $item->id }}" name="judul" required maxlength="180" value="{{ $item->judul }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                                <div><label class="mb-1 block text-xs font-bold text-gray-700" for="deskripsi-{{ $item->id }}">Keterangan</label><textarea id="deskripsi-{{ $item->id }}" name="deskripsi" rows="2" maxlength="3000" class="w-full rounded-lg border-gray-300 text-sm">{{ $item->deskripsi }}</textarea></div>
                                <div><label class="mb-1 block text-xs font-bold text-gray-700" for="file-{{ $item->id }}">Ganti file (opsional)</label><input id="file-{{ $item->id }}" type="file" name="file" accept=".pdf,.doc,.docx" class="w-full rounded-lg border border-gray-300 p-2 text-xs"></div>
                                <label class="flex items-center gap-2 text-xs font-semibold text-gray-700"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($item->is_active) class="rounded border-gray-300 text-red-600 focus:ring-red-500"> Template aktif dan dapat diunduh Admin Cabor</label>
                                <div class="flex flex-wrap gap-2"><button class="rounded-lg bg-[#E60012] px-3 py-2 text-xs font-bold text-white">Simpan Perubahan</button></div>
                            </form>
                            <form method="POST" action="{{ route($routePrefix.'.templates.destroy', $item) }}" class="mt-3" onsubmit="return confirm('Hapus template ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700">Hapus Template</button></form>
                        </details>
                    @endif
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 lg:col-span-2">Belum ada template surat yang tersedia.</div>
            @endforelse
        </div>
        <div class="mt-6">{{ $items->links() }}</div>
    </section>
</div>
@endsection
