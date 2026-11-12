
<form
    action="{{ $pelatih->exists
        ? route('admin.pelatih.update', $pelatih)
        : route('admin.pelatih.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6">

    @csrf

    @if($pelatih->exists)
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


    <!-- BIODATA -->
    <div>
        <h3 class="font-black">Informasi Pelatih</h3>

        <p class="mt-1 text-xs text-gray-500">
            Lengkapi identitas dasar pelatih.
        </p>
    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="nama_lengkap"
                value="{{ old('nama_lengkap', $pelatih->nama_lengkap) }}"
                required
                maxlength="150"
                placeholder="Nama lengkap pelatih"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Cabang Olahraga <span class="text-red-500">*</span>
            </label>

            <select
                name="cabang_olahraga_id"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Cabang Olahraga</option>

                @foreach($cabors as $cabor)

                    <option
                        value="{{ $cabor->id }}"
                        @selected((string) old('cabang_olahraga_id', $pelatih->cabang_olahraga_id) === (string) $cabor->id)
                    >

                        {{ $cabor->nama }}

                        @if($cabor->status === 'nonaktif')
                            (Nonaktif)
                        @endif

                    </option>

                @endforeach

            </select>

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Jenis Kelamin <span class="text-red-500">*</span>
            </label>

            <select
                name="jenis_kelamin"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Jenis Kelamin</option>

                <option value="L"
                    @selected(old('jenis_kelamin', $pelatih->jenis_kelamin) === 'L')>
                    Laki-laki
                </option>

                <option value="P"
                    @selected(old('jenis_kelamin', $pelatih->jenis_kelamin) === 'P')>
                    Perempuan
                </option>

            </select>

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Tempat Lahir
            </label>

            <input
                type="text"
                name="tempat_lahir"
                value="{{ old('tempat_lahir', $pelatih->tempat_lahir) }}"
                maxlength="100"
                placeholder="Contoh: Klaten"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Tanggal Lahir
            </label>

            <input
                type="date"
                name="tanggal_lahir"
                value="{{ old('tanggal_lahir', $pelatih->tanggal_lahir?->format('Y-m-d')) }}"
                max="{{ date('Y-m-d') }}"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Alamat
            </label>

            <textarea
                name="alamat"
                rows="3"
                maxlength="2000"
                placeholder="Alamat lengkap pelatih..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >{{ old('alamat', $pelatih->alamat) }}</textarea>

        </div>

    </div>


    <!-- KONTAK -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Informasi Kontak</h3>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>

            <label class="mb-2 block text-sm font-semibold">
                Nomor Telepon
            </label>

            <input
                type="text"
                name="no_telepon"
                value="{{ old('no_telepon', $pelatih->no_telepon) }}"
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
                required
                value="{{ old('email', $pelatih->email) }}"
                placeholder="email@contoh.com"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >
            <p class="mt-1 text-xs text-gray-500">Email ini menjadi alamat login. Akun dibuat otomatis saat data disimpan.</p>
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

        </div>

    </div>


    <!-- KOMPETENSI -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Kompetensi dan Pengalaman</h3>

        <p class="mt-1 text-xs text-gray-500">
            Informasi pendidikan dan kemampuan profesional pelatih.
        </p>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>

            <label class="mb-2 block text-sm font-semibold">
                Pendidikan Terakhir
            </label>

            <input
                type="text"
                name="pendidikan_terakhir"
                value="{{ old('pendidikan_terakhir', $pelatih->pendidikan_terakhir) }}"
                maxlength="150"
                placeholder="Contoh: S1 Pendidikan Olahraga"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Spesialisasi
            </label>

            <input
                type="text"
                name="spesialisasi"
                value="{{ old('spesialisasi', $pelatih->spesialisasi) }}"
                maxlength="150"
                placeholder="Contoh: Pelatih teknik dasar"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Pengalaman Melatih (Tahun)
            </label>

            <input
                type="number"
                name="pengalaman_tahun"
                value="{{ old('pengalaman_tahun', $pelatih->pengalaman_tahun) }}"
                min="0"
                max="60"
                placeholder="Contoh: 5"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Status Pelatih
            </label>

            <select
                name="status"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="aktif"
                    @selected(old('status', $pelatih->status ?: 'aktif') === 'aktif')>
                    Aktif
                </option>

                <option value="nonaktif"
                    @selected(old('status', $pelatih->status) === 'nonaktif')>
                    Nonaktif
                </option>

            </select>

        </div>


        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Sertifikasi
            </label>

            <textarea
                name="sertifikasi"
                rows="4"
                maxlength="5000"
                placeholder="Contoh: Lisensi kepelatihan tingkat nasional..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >{{ old('sertifikasi', $pelatih->sertifikasi) }}</textarea>

        </div>

    </div>


    <!-- FOTO -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Foto Pelatih</h3>

        @if($pelatih->foto_path)

            <img
                src="{{ asset('storage/'.$pelatih->foto_path) }}"
                alt="{{ $pelatih->nama_lengkap }}"
                class="mb-4 mt-4 h-28 w-28 rounded-xl border border-gray-200 object-cover"
            >

        @endif

        <input
            type="file"
            name="foto"
            accept=".jpg,.jpeg,.png,.webp,image/*"
            class="mt-4 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
        >

        <p class="mt-2 text-xs text-gray-400">
            Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
        </p>

    </div>


    <!-- BUTTON -->
    <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">

        <a
            href="{{ route('admin.pelatih.index') }}"
            class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-bold text-gray-600 hover:bg-gray-50">

            Batal

        </a>


        <button
            type="submit"
            class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

            {{ $pelatih->exists ? 'Simpan Perubahan' : 'Simpan Pelatih' }}

        </button>

    </div>

</form>
