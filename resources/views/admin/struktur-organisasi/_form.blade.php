
<form
    action="{{ $pengurus->exists
        ? route('admin.struktur-organisasi.update', $pengurus)
        : route('admin.struktur-organisasi.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6">

    @csrf

    @if($pengurus->exists)
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


    <!-- ORGANISASI -->
    <div>

        <h3 class="font-black">Informasi Organisasi</h3>

        <p class="mt-1 text-xs text-gray-500">
            Tentukan pengurus berada pada tingkat KONI atau cabang olahraga.
        </p>

    </div>


    <div>

        <label class="mb-2 block text-sm font-semibold">
            Tingkat Organisasi <span class="text-red-500">*</span>
        </label>

        <select
            name="cabang_olahraga_id"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

            <option
                value=""
                @selected(old('cabang_olahraga_id', $pengurus->cabang_olahraga_id) == '')>
                Pengurus KONI Kabupaten Klaten
            </option>

            @foreach($cabors as $cabor)

                <option
                    value="{{ $cabor->id }}"
                    @selected((string) old('cabang_olahraga_id', $pengurus->cabang_olahraga_id) === (string) $cabor->id)
                >

                    Pengurus {{ $cabor->nama }}

                    @if($cabor->status === 'nonaktif')
                        (Nonaktif)
                    @endif

                </option>

            @endforeach

        </select>

        <p class="mt-2 text-xs text-gray-400">
            Pilih KONI untuk pengurus tingkat kabupaten, atau pilih cabor untuk pengurus cabang olahraga.
        </p>

    </div>


    <!-- BIODATA -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Biodata Pengurus</h3>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="nama_lengkap"
                value="{{ old('nama_lengkap', $pengurus->nama_lengkap) }}"
                required
                maxlength="150"
                placeholder="Nama lengkap pengurus"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Jabatan <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="jabatan"
                value="{{ old('jabatan', $pengurus->jabatan) }}"
                required
                maxlength="120"
                placeholder="Contoh: Ketua Umum"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Bidang
            </label>

            <input
                type="text"
                name="bidang"
                value="{{ old('bidang', $pengurus->bidang) }}"
                maxlength="120"
                placeholder="Contoh: Bidang Pembinaan Prestasi"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Jenis Kelamin
            </label>

            <select
                name="jenis_kelamin"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Jenis Kelamin</option>

                <option value="L"
                    @selected(old('jenis_kelamin', $pengurus->jenis_kelamin) === 'L')>
                    Laki-laki
                </option>

                <option value="P"
                    @selected(old('jenis_kelamin', $pengurus->jenis_kelamin) === 'P')>
                    Perempuan
                </option>

            </select>

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Nomor Telepon
            </label>

            <input
                type="text"
                name="no_telepon"
                value="{{ old('no_telepon', $pengurus->no_telepon) }}"
                maxlength="30"
                placeholder="08xxxxxxxxxx"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $pengurus->email) }}"
                placeholder="email@contoh.com"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>

    </div>


    <!-- PERIODE -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Periode Kepengurusan</h3>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>

            <label class="mb-2 block text-sm font-semibold">
                Tahun Mulai
            </label>

            <input
                type="number"
                name="periode_mulai"
                value="{{ old('periode_mulai', $pengurus->periode_mulai) }}"
                min="1900"
                max="2100"
                placeholder="Contoh: 2025"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Tahun Selesai
            </label>

            <input
                type="number"
                name="periode_selesai"
                value="{{ old('periode_selesai', $pengurus->periode_selesai) }}"
                min="1900"
                max="2100"
                placeholder="Contoh: 2029"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>

    </div>


    <!-- FOTO -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Foto Pengurus</h3>

        @if($pengurus->foto_path)

            <img
                src="{{ asset('storage/'.$pengurus->foto_path) }}"
                alt="{{ $pengurus->nama_lengkap }}"
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


    <!-- PENGATURAN -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Pengaturan Tampilan</h3>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>

            <label class="mb-2 block text-sm font-semibold">
                Urutan Tampil
            </label>

            <input
                type="number"
                name="urutan"
                value="{{ old('urutan', $pengurus->urutan ?? 0) }}"
                min="0"
                max="999"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

            <p class="mt-1 text-xs text-gray-400">
                Angka lebih kecil akan tampil lebih dahulu.
            </p>

        </div>


        <div>

            <label class="mb-2 block text-sm font-semibold">
                Status
            </label>

            <select
                name="status"
                required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="aktif"
                    @selected(old('status', $pengurus->status ?: 'aktif') === 'aktif')>
                    Aktif
                </option>

                <option value="nonaktif"
                    @selected(old('status', $pengurus->status) === 'nonaktif')>
                    Nonaktif
                </option>

            </select>

        </div>

    </div>


    <!-- BUTTON -->
    <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">

        <a
            href="{{ route('admin.struktur-organisasi.index') }}"
            class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-bold text-gray-600">

            Batal

        </a>


        <button
            type="submit"
            class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

            {{ $pengurus->exists ? 'Simpan Perubahan' : 'Simpan Pengurus' }}

        </button>

    </div>

</form>
