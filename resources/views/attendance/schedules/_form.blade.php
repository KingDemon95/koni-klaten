@php
    $existingSlots = $schedule?->slots ?? collect();
@endphp

@if(session('success'))
    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p class="font-bold mb-2">Periksa kembali pengaturan jadwal:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="mb-5">
        <h3 class="text-lg font-black text-gray-800">{{ $heading }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
    </div>

    <form action="{{ $action }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-6">
            <label class="mb-2 block text-sm font-bold text-gray-700">
                Mulai Berlaku
            </label>

            <input
                type="date"
                name="effective_from"
                value="{{ old('effective_from', now()->addDay()->toDateString()) }}"
                min="{{ now()->addDay()->toDateString() }}"
                required
                class="w-full max-w-sm rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-red-500 focus:outline-none"
            >

            <p class="mt-1 text-xs text-gray-500">
                Jadwal baru berlaku mulai tanggal yang dipilih.
                Riwayat jadwal sebelumnya tetap tersimpan.
            </p>
        </div>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="font-bold text-gray-800">Pengaturan Hari dan Jam</h4>
                <p class="text-xs text-gray-500">
                    {{ $oneSlotPerDay
                        ? 'Satu jadwal kerja untuk setiap hari.'
                        : 'Tambahkan beberapa sesi latihan dalam satu hari.' }}
                </p>
            </div>

            <button
                type="button"
                id="add-slot"
                class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-gray-700"
            >
                + Tambah {{ $oneSlotPerDay ? 'Hari Kerja' : 'Sesi Latihan' }}
            </button>
        </div>

        <div id="slot-container" class="space-y-3">
            @foreach($existingSlots as $i => $slot)
                <div class="slot-row rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-600">Hari</label>
                            <select name="slots[{{ $i }}][day_of_week]" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                                @foreach($days as $number => $day)
                                    <option value="{{ $number }}"
                                        @selected(old("slots.$i.day_of_week", $slot->day_of_week) == $number)>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(!$oneSlotPerDay)
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Nama Sesi</label>
                                <input type="text"
                                    name="slots[{{ $i }}][label]"
                                    value="{{ old("slots.$i.label", $slot->label) }}"
                                    placeholder="Contoh: Latihan Pagi"
                                    maxlength="80"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                            </div>
                        @else
                            <div>
                                <label class="mb-1 block text-xs font-bold text-gray-600">Keterangan</label>
                                <input type="text"
                                    name="slots[{{ $i }}][label]"
                                    value="{{ old("slots.$i.label", $slot->label) }}"
                                    placeholder="Contoh: Jam Kerja"
                                    maxlength="80"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                            </div>
                        @endif

                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-600">Jam Mulai</label>
                            <input type="time"
                                name="slots[{{ $i }}][starts_at]"
                                value="{{ old("slots.$i.starts_at", substr($slot->starts_at, 0, 5)) }}"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-600">Jam Selesai</label>
                            <input type="time"
                                name="slots[{{ $i }}][ends_at]"
                                value="{{ old("slots.$i.ends_at", substr($slot->ends_at, 0, 5)) }}"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <button type="button"
                            class="remove-slot text-xs font-bold text-red-600 hover:text-red-800">
                            Hapus baris
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="empty-message"
            class="{{ $existingSlots->isEmpty() ? '' : 'hidden' }} rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
            Belum ada jadwal. Klik tombol tambah untuk memulai.
        </div>

        <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5">
            <button type="submit"
                class="rounded-xl bg-red-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-red-700">
                Simpan Jadwal
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('slot-container');
    const addButton = document.getElementById('add-slot');
    const emptyMessage = document.getElementById('empty-message');
    const days = @json($days);
    const oneSlotPerDay = @json($oneSlotPerDay);

    let nextIndex = {{ $existingSlots->count() }};

    function refreshEmpty() {
        emptyMessage.classList.toggle('hidden', container.children.length > 0);
    }

    function addSlot() {
        const index = nextIndex++;
        const row = document.createElement('div');
        row.className = 'slot-row rounded-xl border border-gray-200 bg-gray-50 p-4';

        const dayOptions = Object.entries(days).map(([number, day]) =>
            `<option value="${number}">${day}</option>`
        ).join('');

        row.innerHTML = `
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-600">Hari</label>
                    <select name="slots[${index}][day_of_week]" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                        ${dayOptions}
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-600">
                        ${oneSlotPerDay ? 'Keterangan' : 'Nama Sesi'}
                    </label>
                    <input type="text" name="slots[${index}][label]"
                        maxlength="80"
                        placeholder="${oneSlotPerDay ? 'Jam Kerja' : 'Contoh: Latihan Pagi'}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-600">Jam Mulai</label>
                    <input type="time" name="slots[${index}][starts_at]" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold text-gray-600">Jam Selesai</label>
                    <input type="time" name="slots[${index}][ends_at]" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm">
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button type="button"
                    class="remove-slot text-xs font-bold text-red-600 hover:text-red-800">
                    Hapus baris
                </button>
            </div>
        `;

        container.appendChild(row);
        refreshEmpty();
    }

    addButton.addEventListener('click', addSlot);

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-slot');

        if (!button) return;

        button.closest('.slot-row').remove();
        refreshEmpty();
    });

    refreshEmpty();
});
</script>
