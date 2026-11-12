@extends($layout)

@section('title', 'Laporan & Rekapitulasi')
@section('heading', 'Laporan & Rekapitulasi')

@section('content')
@php
    $exportFilters = array_filter([
        'tahun' => $year,
        'cabor_id' => $isCaborAdmin ? null : $selectedCaborId,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="space-y-7">
    <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#A90010] to-[#E60012] p-6 text-white shadow-lg sm:p-8">
        <p class="text-xs font-bold tracking-[0.22em] text-yellow-300">PUSAT DATA KONI KLATEN</p>
        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-black sm:text-3xl">Laporan & Rekapitulasi</h2>
                <p class="mt-2 text-sm leading-6 text-red-100">
                    Ringkasan data cabang olahraga, atlet, pelatih, dan prestasi yang sudah diverifikasi.
                    @if($isCaborAdmin && $cabor)
                        Laporan dibatasi untuk {{ $cabor->nama }}.
                    @else
                        Gunakan filter untuk melihat periode atau cabang olahraga tertentu.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route($routePrefix.'.laporan.export', ['jenis' => 'cabor'] + $exportFilters) }}"
                   class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-xs font-black text-[#A90010] transition hover:bg-red-50">
                    Unduh Rekap Cabor (CSV)
                </a>
                <a href="{{ route($routePrefix.'.laporan.export', ['jenis' => 'prestasi'] + $exportFilters) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-white/40 bg-white/10 px-4 py-3 text-xs font-bold text-white transition hover:bg-white/20">
                    Unduh Prestasi (CSV)
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4">
            <h3 class="font-black text-gray-900">Filter laporan</h3>
            <p class="mt-1 text-xs text-gray-500">Filter yang sama digunakan pada tampilan dan file CSV.</p>
        </div>

        <form method="GET" action="{{ route($routePrefix.'.laporan.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
            <div>
                <label for="tahun" class="mb-2 block text-xs font-bold text-gray-600">Tahun prestasi</label>
                <select id="tahun" name="tahun" class="w-full rounded-xl border-gray-300 px-3 py-3 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Semua tahun</option>
                    @foreach($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                    @endforeach
                    @if($year && !$availableYears->contains($year))
                        <option value="{{ $year }}" selected>{{ $year }}</option>
                    @endif
                </select>
                @error('tahun') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @unless($isCaborAdmin)
                <div>
                    <label for="cabor_id" class="mb-2 block text-xs font-bold text-gray-600">Cabang olahraga</label>
                    <select id="cabor_id" name="cabor_id" class="w-full rounded-xl border-gray-300 px-3 py-3 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">Semua cabang olahraga aktif</option>
                        @foreach($caborOptions as $option)
                            <option value="{{ $option->id }}" @selected($selectedCaborId === $option->id)>{{ $option->nama }}</option>
                        @endforeach
                    </select>
                    @error('cabor_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endunless

            <button type="submit" class="rounded-xl bg-[#D71920] px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">Terapkan</button>
            <a href="{{ route($routePrefix.'.laporan.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-center text-sm font-bold text-gray-600 transition hover:bg-gray-50">Reset</a>
        </form>
    </section>

    <section>
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h3 class="text-lg font-black text-gray-900">Ringkasan organisasi</h3>
                <p class="mt-1 text-xs text-gray-500">
                    @if($year) Periode tahun {{ $year }}. @else Seluruh tahun yang tercatat. @endif
                    Data hanya menghitung cabang olahraga aktif. Filter tahun berlaku untuk prestasi; atlet dan pelatih menunjukkan jumlah aktif saat ini.
                </p>
            </div>
            <span class="rounded-full bg-green-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-green-700">Data langsung</span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
            @foreach([
                ['label' => 'Cabor aktif', 'value' => $stats['cabor_aktif']],
                ['label' => 'Atlet aktif', 'value' => $stats['atlet_aktif']],
                ['label' => 'Pelatih aktif', 'value' => $stats['pelatih_aktif']],
                ['label' => 'Prestasi terverifikasi', 'value' => $stats['prestasi_terverifikasi']],
                ['label' => 'Medali emas', 'value' => $stats['emas']],
                ['label' => 'Medali perak', 'value' => $stats['perak']],
                ['label' => 'Medali perunggu', 'value' => $stats['perunggu']],
                ['label' => 'Menunggu verifikasi', 'value' => $stats['menunggu_verifikasi']],
            ] as $stat)
                <article class="rounded-2xl border border-gray-200 border-l-4 border-l-[#D71920] bg-white p-4 shadow-sm sm:p-5">
                    <p class="text-xs font-semibold text-gray-500">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-black tabular-nums text-gray-900 sm:text-3xl">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5 sm:px-6">
            <div>
                <h3 class="font-black text-gray-900">Rekap per cabang olahraga</h3>
                <p class="mt-1 text-xs text-gray-500">Jumlah atlet dan pelatih aktif serta prestasi terverifikasi.</p>
            </div>
            <a href="{{ route($routePrefix.'.laporan.export', ['jenis' => 'cabor'] + $exportFilters) }}" class="text-xs font-black text-[#D71920] hover:underline">Unduh CSV →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-bold">Cabang olahraga</th>
                        <th class="px-4 py-3 text-right font-bold">Atlet aktif</th>
                        <th class="px-4 py-3 text-right font-bold">Pelatih aktif</th>
                        <th class="px-4 py-3 text-right font-bold">Prestasi</th>
                        <th class="px-4 py-3 text-right font-bold">Emas</th>
                        <th class="px-4 py-3 text-right font-bold">Perak</th>
                        <th class="px-4 py-3 text-right font-bold">Perunggu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cabors as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-bold text-gray-900">{{ $item->nama }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">{{ number_format($item->atlet_aktif_count, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">{{ number_format($item->pelatih_aktif_count, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black tabular-nums">{{ number_format($item->prestasi_terverifikasi_count, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">{{ number_format($item->medali_emas_count, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">{{ number_format($item->medali_perak_count, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">{{ number_format($item->medali_perunggu_count, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada cabang olahraga aktif pada cakupan laporan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5 sm:px-6">
            <div>
                <h3 class="font-black text-gray-900">Prestasi terverifikasi terbaru</h3>
                <p class="mt-1 text-xs text-gray-500">Hanya prestasi yang sudah disahkan Admin Cabor dan berasal dari cabor aktif.</p>
            </div>
            <a href="{{ route($routePrefix.'.laporan.export', ['jenis' => 'prestasi'] + $exportFilters) }}" class="text-xs font-black text-[#D71920] hover:underline">Unduh seluruh data CSV →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[680px] w-full text-left text-sm">
                <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-bold">Kejuaraan</th>
                        <th class="px-4 py-3 font-bold">Atlet / Pelatih</th>
                        <th class="px-4 py-3 font-bold">Cabor</th>
                        <th class="px-4 py-3 font-bold">Hasil</th>
                        <th class="px-4 py-3 font-bold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentPrestasis as $prestasi)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-gray-900">{{ $prestasi->nama_kejuaraan }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ ucfirst($prestasi->tingkat) }}@if($prestasi->jenis_medali) · Medali {{ ucfirst($prestasi->jenis_medali) }}@endif</p>
                            </td>
                            <td class="px-4 py-4 text-gray-700">{{ $prestasi->atlet?->nama_lengkap ?? $prestasi->pelatih?->nama_lengkap ?? 'Nama tidak tersedia' }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ $prestasi->cabangOlahraga?->nama ?? '-' }}</td>
                            <td class="px-4 py-4 font-semibold text-gray-800">{{ $prestasi->hasil }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-500">{{ $prestasi->tanggal_prestasi?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada prestasi terverifikasi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p class="text-[11px] leading-5 text-gray-400">
        Catatan: laporan tidak menyertakan alamat, nomor telepon, email, maupun dokumen bukti privat. File CSV menggunakan UTF-8 dan dapat dibuka melalui aplikasi spreadsheet.
    </p>
</div>
@endsection
