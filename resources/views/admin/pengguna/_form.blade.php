
@php

    $currentRole = old(
        'role',
        $pengguna->roles->first()?->name ?? 'staff_koni'
    );

    $selectedCabor = old(
        'cabang_olahraga_id',
        $pengguna->cabang_olahraga_id
    );

    $selectedAtlet = old(
        'atlet_id',
        $pengguna->atlet?->id
    );

    $selectedPelatih = old(
        'pelatih_id',
        $pengguna->pelatih?->id
    );

    $isActive = old(
        'is_active',
        $pengguna->exists ? $pengguna->is_active : true
    );

@endphp


@if($errors->any())

    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">

        <p class="font-bold text-red-700">
            Terdapat kesalahan pada formulir:
        </p>

        <ul class="mt-2 list-inside list-disc text-sm text-red-600">

            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach

        </ul>

    </div>

@endif


<div class="grid gap-6 lg:grid-cols-2">


    <!-- INFORMASI AKUN -->
    <div class="rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-5 border-b border-gray-100 pb-4 font-black">
            Informasi Akun
        </h3>


        <!-- NAME -->
        <div class="mb-5">

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Nama Pengguna *
            </label>

            <input
                type="text"
                name="name"
                value="{{ old('name', $pengguna->name) }}"
                required
                maxlength="255"
                placeholder="Masukkan nama pengguna"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >

            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- EMAIL -->
        <div class="mb-5">

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Email *
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $pengguna->email) }}"
                required
                maxlength="255"
                placeholder="contoh@email.com"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100"
            >

            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- ROLE -->
        <div class="mb-5">

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Role Pengguna *
            </label>

            <select
                name="role"
                id="pengguna-role"
                required
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Role</option>

                <option value="admin_koni" @selected($currentRole === 'admin_koni')}>
                    Admin KONI
                </option>

                <option value="staff_koni" @selected($currentRole === 'staff_koni')}>
                    Staff KONI
                </option>

                <option value="admin_cabor" @selected($currentRole === 'admin_cabor')}>
                    Admin Cabor
                </option>

                <option value="atlet" @selected($currentRole === 'atlet')}>
                    Atlet
                </option>

                <option value="pelatih" @selected($currentRole === 'pelatih')}>
                    Pelatih
                </option>

                @if($pengguna->exists && $pengguna->hasRole('atlet_pelatih'))

                    <option value="atlet_pelatih" @selected($currentRole === 'atlet_pelatih')}>
                        Atlet / Pelatih (Akun Lama)
                    </option>

                @endif

            </select>

            @error('role')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- PASSWORD -->
        <div class="mb-5">

            <label class="mb-2 block text-sm font-bold text-gray-700">

                Password
                @if(!$pengguna->exists)
                    *
                @else
                    (Opsional)
                @endif

            </label>

            <input
                type="password"
                name="password"
                {{ !$pengguna->exists ? 'required' : '' }}
                minlength="8"
                autocomplete="new-password"
                placeholder="{{ $pengguna->exists ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- PASSWORD CONFIRMATION -->
        <div>

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Konfirmasi Password
            </label>

            <input
                type="password"
                name="password_confirmation"
                {{ !$pengguna->exists ? 'required' : '' }}
                minlength="8"
                autocomplete="new-password"
                placeholder="Ulangi password"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500"
            >

        </div>

    </div>


    <!-- RELASI PROFIL -->
    <div class="rounded-xl border border-gray-200 bg-white p-6">

        <h3 class="mb-2 border-b border-gray-100 pb-4 font-black">
            Hubungan Data Pengguna
        </h3>

        <p class="mb-6 text-xs text-gray-500">
            Pilihan berikut akan menyesuaikan role pengguna.
        </p>


        <!-- CABOR -->
        <div id="section-cabor"
             data-role-section="admin_cabor"
             class="mb-5 hidden">

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Cabang Olahraga *
            </label>

            <select
                name="cabang_olahraga_id"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Cabang Olahraga</option>

                @foreach($cabors as $cabor)

                    <option
                        value="{{ $cabor->id }}"
                        @selected((string) $selectedCabor === (string) $cabor->id)>

                        {{ $cabor->nama }}

                    </option>

                @endforeach

            </select>

            @error('cabang_olahraga_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- ATLET -->
        <div id="section-atlet"
             data-role-section="atlet"
             class="mb-5 hidden">

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Profil Atlet *
            </label>

            <select
                name="atlet_id"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Profil Atlet</option>

                @foreach($atlets as $atlet)

                    <option
                        value="{{ $atlet->id }}"
                        @selected((string) $selectedAtlet === (string) $atlet->id)>

                        {{ $atlet->nama_lengkap }}
                        — {{ $atlet->cabangOlahraga?->nama ?? 'Tanpa Cabor' }}

                    </option>

                @endforeach

            </select>

            @error('atlet_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- PELATIH -->
        <div id="section-pelatih"
             data-role-section="pelatih"
             class="mb-5 hidden">

            <label class="mb-2 block text-sm font-bold text-gray-700">
                Profil Pelatih *
            </label>

            <select
                name="pelatih_id"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-red-500">

                <option value="">Pilih Profil Pelatih</option>

                @foreach($pelatihs as $pelatih)

                    <option
                        value="{{ $pelatih->id }}"
                        @selected((string) $selectedPelatih === (string) $pelatih->id)>

                        {{ $pelatih->nama_lengkap }}
                        — {{ $pelatih->cabangOlahraga?->nama ?? 'Tanpa Cabor' }}

                    </option>

                @endforeach

            </select>

            @error('pelatih_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

        </div>


        <!-- INFORMATION -->
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">

            <p class="text-sm font-bold text-blue-800">
                Informasi Relasi Akun
            </p>

            <p class="mt-2 text-xs leading-relaxed text-blue-700">
                Admin Cabor akan terhubung dengan satu cabang olahraga.
                Atlet dan Pelatih akan terhubung dengan profil yang sudah
                tersedia di database.
            </p>

            <p class="mt-2 text-xs leading-relaxed text-blue-700">
                Admin KONI dan Staff KONI tidak membutuhkan hubungan
                dengan cabang olahraga.
            </p>

        </div>

    </div>

</div>


<!-- STATUS -->
<div class="mt-6 rounded-xl border border-gray-200 bg-white p-6">

    <h3 class="mb-4 font-black">
        Status Akun
    </h3>

    <label class="flex cursor-pointer items-center gap-3">

        <input
            type="hidden"
            name="is_active"
            value="0"
        >

        <input
            type="checkbox"
            name="is_active"
            value="1"
            @checked((bool) $isActive)
            class="h-5 w-5 rounded border-gray-300 text-[#E60012] focus:ring-red-500"
        >

        <span>

            <span class="block text-sm font-bold text-gray-800">
                Aktifkan akun
            </span>

            <span class="mt-1 block text-xs text-gray-500">
                Pengguna dapat menggunakan akun ketika statusnya aktif.
            </span>

        </span>

    </label>

    @error('is_active')
        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
    @enderror

</div>


<!-- ACTION BUTTONS -->
<div class="mt-7 flex flex-col-reverse justify-end gap-3 sm:flex-row">

    <a href="{{ route('admin.pengguna.index') }}"
       class="rounded-xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-bold text-gray-600 transition hover:bg-gray-50">

        Batal

    </a>

    <button
        type="submit"
        class="rounded-xl bg-[#E60012] px-7 py-3 text-sm font-bold text-white shadow-lg shadow-red-100 transition hover:bg-red-700">

        {{ $pengguna->exists ? 'Simpan Perubahan' : 'Simpan Pengguna' }}

    </button>

</div>


<!-- DYNAMIC ROLE FORM -->
<script>
(function () {

    const roleSelect = document.getElementById('pengguna-role');

    if (!roleSelect) return;

    const sections = document.querySelectorAll('[data-role-section]');

    function updateRoleFields() {

        const selectedRole = roleSelect.value;

        sections.forEach(function (section) {

            const shouldShow =
                section.dataset.roleSection === selectedRole;

            section.classList.toggle('hidden', !shouldShow);

            const select = section.querySelector('select');

            if (select) {
                select.disabled = !shouldShow;
            }

        });

    }

    roleSelect.addEventListener('change', updateRoleFields);

    updateRoleFields();

})();
</script>
