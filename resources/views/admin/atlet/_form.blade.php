
<form
    action="{{ $atlet->exists
        ? route('admin.atlet.update', $atlet)
        : route('admin.atlet.store') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-6">

    @csrf

    @if($atlet->exists)
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


    <!-- DATA UTAMA -->
    <div>

        <h3 class="font-black">Informasi Atlet</h3>

        <p class="mt-1 text-xs text-gray-500">
            Masukkan identitas dasar atlet.
        </p>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <!-- NAMA -->
        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>

            <input
                type="text"
                name="nama_lengkap"
                value="{{ old('nama_lengkap', $atlet->nama_lengkap) }}"
                required
                maxlength="150"
                placeholder="Nama lengkap atlet"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <!-- CABOR -->
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
                        @selected((string) old('cabang_olahraga_id', $atlet->cabang_olahraga_id) === (string) $cabor->id)
                    >

                        {{ $cabor->nama }}

                        @if($cabor->status === 'nonaktif')
                            (Nonaktif)
                        @endif

                    </option>

                @endforeach

            </select>

        </div>


        <!-- GENDER -->
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
                    @selected(old('jenis_kelamin', $atlet->jenis_kelamin) === 'L')>
                    Laki-laki
                </option>

                <option value="P"
                    @selected(old('jenis_kelamin', $atlet->jenis_kelamin) === 'P')>
                    Perempuan
                </option>

            </select>

        </div>


        <!-- TEMPAT LAHIR -->
        <div>

            <label class="mb-2 block text-sm font-semibold">
                Tempat Lahir
            </label>

            <input
                type="text"
                name="tempat_lahir"
                value="{{ old('tempat_lahir', $atlet->tempat_lahir) }}"
                maxlength="100"
                placeholder="Contoh: Klaten"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <!-- TANGGAL LAHIR -->
        <div>

            <label class="mb-2 block text-sm font-semibold">
                Tanggal Lahir
            </label>

            <input
                type="date"
                name="tanggal_lahir"
                value="{{ old('tanggal_lahir', $atlet->tanggal_lahir?->format('Y-m-d')) }}"
                max="{{ date('Y-m-d') }}"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>


        <!-- ALAMAT -->
        <div class="md:col-span-2">

            <label class="mb-2 block text-sm font-semibold">
                Alamat
            </label>

            <textarea
                name="alamat"
                rows="3"
                maxlength="2000"
                placeholder="Alamat lengkap atlet..."
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >{{ old('alamat', $atlet->alamat) }}</textarea>

        </div>

    </div>


    <!-- CONTACT -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Informasi Kontak</h3>

        <p class="mt-1 text-xs text-gray-500">
            Informasi yang dapat digunakan untuk menghubungi atlet.
        </p>

    </div>


    <div class="grid gap-5 md:grid-cols-2">

        <div>

            <label class="mb-2 block text-sm font-semibold">
                Nomor Telepon
            </label>

            <input
                type="text"
                name="no_telepon"
                value="{{ old('no_telepon', $atlet->no_telepon) }}"
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
                value="{{ old('email', $atlet->email) }}"
                placeholder="email@contoh.com"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>

    </div>


    <!-- PHOTO -->
    <div class="border-t border-gray-100 pt-6">

        <h3 class="font-black">Foto Atlet</h3>

        <div class="mt-4">

            @if($atlet->foto_path)

                <img
                    src="{{ asset('storage/'.$atlet->foto_path) }}"
                    alt="{{ $atlet->nama_lengkap }}"
                    class="mb-4 h-28 w-28 rounded-xl border border-gray-200 object-cover"
                >

            @endif

            <input
                type="file"
                name="foto"
                accept=".jpg,.jpeg,.png,.webp,image/*"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm"
            >

            <p class="mt-2 text-xs text-gray-400">
                Format JPG, JPEG, PNG, WEBP. Maksimal 2 MB.
            </p>

        </div>

    </div>


    <!-- STATUS -->
    <div class="border-t border-gray-100 pt-6">

        <label class="mb-2 block text-sm font-semibold">
            Status Atlet
        </label>

        <select
            name="status"
            required
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

            <option value="aktif"
                @selected(old('status', $atlet->status ?: 'aktif') === 'aktif')>
                Aktif
            </option>

            <option value="nonaktif"
                @selected(old('status', $atlet->status) === 'nonaktif')>
                Nonaktif
            </option>

        </select>

    </div>


    <!-- BUTTON -->
    <div class="flex flex-col-reverse justify-end gap-3 border-t border-gray-100 pt-6 sm:flex-row">

        <a
            href="{{ route('admin.atlet.index') }}"
            class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-bold text-gray-600 hover:bg-gray-50">

            Batal

        </a>


        <button
            type="submit"
            class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 hover:bg-red-700">

            {{ $atlet->exists ? 'Simpan Perubahan' : 'Simpan Atlet' }}

        </button>

    </div>

</form>
