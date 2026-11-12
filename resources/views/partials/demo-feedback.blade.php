{{-- Pesan tindakan dan ringkasan kesalahan, hanya memakai escaping Blade standar. --}}
@if(session()->has('error'))
    <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm">
        <p class="font-bold">Tindakan belum berhasil</p>
        <p class="mt-1">{{ session('error') }}</p>
    </div>
@endif

@if(session()->has('warning'))
    <div role="status" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm">
        <p class="font-bold">Perlu perhatian</p>
        <p class="mt-1">{{ session('warning') }}</p>
    </div>
@endif

@if($errors->any())
    <div role="alert" aria-labelledby="koni-validation-title" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-900 shadow-sm">
        <p id="koni-validation-title" class="font-black">Periksa kembali data yang diisi</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
