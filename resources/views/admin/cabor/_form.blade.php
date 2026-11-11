
<form
    action="{{ $cabor->exists
        ? route('admin.cabor.update', $cabor)
        : route('admin.cabor.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6">

    @csrf

    @if($cabor->exists)
        @method('PUT')
    @endif


    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-bold">Periksa kembali data berikut:</p>

            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- IDENTITAS CABOR -->
    <div>
        <h3 class="font-black">Identitas Cabang Olahraga</h3>

        <p class="mt-1 text-xs text-gray-500">
            Informasi dasar cabang olahraga.
        </p>
    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>
            <label class="mb-2 block text-sm font-semibold">
                Nama Cabang Olahraga <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="nama"
                value="{{ old('nama', $cabor->nama) }}"
                required
                maxlength="150"
                placeholder="Contoh: Bulutangkis"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >

            @error('nama')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>


        <div>
            <label class="mb-2 block text-sm font-semibold">
                Singkatan
            </label>

            <input
                type="text"
                name="singkatan"
                value="{{ old('singkatan', $cabor->singkatan) }}"
                maxlength="30"
                placeholder="Contoh: PBSI"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >
        </div>


        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Deskripsi
            </label>

            <textarea
                name="deskripsi"
                rows="4"
                maxlength="3000"
                placeholder="Informasi singkat tentang cabang olahraga..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >{{ old('deskripsi', $cabor->deskripsi) }}</textarea>

        </div>


        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Logo / Foto Cabor
            </label>

            @if($cabor->logo_path)
                <div class="mb-3">
                    <img
                        src="{{ asset('storage/'.$cabor->logo_path) }}"
                        alt="Logo {{ $cabor->nama }}"
                        class="h-24 w-24 rounded-xl border border-gray-200 bg-white object-contain p-2"
                    >

                    <p class="mt-1 text-xs text-gray-400">
                        Logo saat ini.
                    </p>
                </div>
            @endif

            <input
                type="file"
                name="logo"
                accept=".jpg,.jpeg,.png,.webp,image/*"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
            >

            <p class="mt-2 text-xs text-gray-400">
                Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
            </p>

            @error('logo')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>

    </div>


    <div class="border-t border-gray-100 pt-6">
        <h3 class="font-black">Informasi Pengurus</h3>

        <p class="mt-1 text-xs text-gray-500">
            Informasi kontak pengurus cabang olahraga.
        </p>
    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>
            <label class="mb-2 block text-sm font-semibold">
                Nama Ketua
            </label>

            <input
                type="text"
                name="nama_ketua"
                value="{{ old('nama_ketua', $cabor->nama_ketua) }}"
                maxlength="150"
                placeholder="Nama ketua cabor"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >
        </div>


        <div>
            <label class="mb-2 block text-sm font-semibold">
                Nomor Telepon
            </label>

            <input
                type="text"
                name="telepon"
                value="{{ old('telepon', $cabor->telepon) }}"
                maxlength="30"
                placeholder="08xxxxxxxxxx"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >
        </div>


        <div>
            <label class="mb-2 block text-sm font-semibold">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $cabor->email) }}"
                placeholder="email@contoh.com"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >
        </div>


        <div>
            <label class="mb-2 block text-sm font-semibold">
                Status Cabor
            </label>

            <select
                name="status"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="aktif"
                    @selected(old('status', $cabor->status ?: 'aktif') === 'aktif')>
                    Aktif
                </option>

                <option value="nonaktif"
                    @selected(old('status', $cabor->status) === 'nonaktif')>
                    Nonaktif
                </option>

            </select>
        </div>


        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Alamat Sekretariat
            </label>

            <textarea
                name="alamat"
                rows="3"
                maxlength="2000"
                placeholder="Alamat sekretariat cabor..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >{{ old('alamat', $cabor->alamat) }}</textarea>

        </div>

    </div>


    <!-- BUTTON -->
    <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">

        <a
            href="{{ route('admin.cabor.index') }}"
            class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-bold text-gray-600 hover:bg-gray-50">

            Batal

        </a>


        <button
            type="submit"
            class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 transition hover:bg-red-700">

            {{ $cabor->exists ? 'Simpan Perubahan' : 'Simpan Cabor' }}

        </button>

    </div>

</form>
