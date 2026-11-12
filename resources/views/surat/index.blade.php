@extends($layout)

@section('title', 'Persuratan KONI')
@section('heading', 'Persuratan')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif

    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#3b1015] p-6 text-white shadow-sm">
        <p class="text-xs font-bold tracking-[0.2em] text-yellow-300">SISTEM ADMINISTRASI KONI KLATEN</p>
        <h2 class="mt-2 text-2xl font-black">Manajemen Persuratan</h2>
        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-200">
            Surat dari Admin Cabor wajib diperiksa Staff KONI terlebih dahulu sebelum diteruskan kepada Admin KONI. Riwayat proses dan keputusan tercatat pada setiap surat.
        </p>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Total Surat', $stats['total']], ['Perlu Tindak Lanjut', $stats['menunggu']], ['Perlu Revisi Cabor', $stats['revisi']], ['Selesai', $stats['selesai']]] as [$label, $value])
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black text-gray-900">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-black">Daftar Surat</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if ($prefix === 'cabor')
                        Surat yang dibuat oleh cabang olahraga {{ $cabor?->nama }}.
                    @elseif ($prefix === 'staff')
                        Antrean pemeriksaan, penerusan, dan penyampaian hasil surat.
                    @else
                        Surat yang telah diteruskan oleh Staff KONI untuk keputusan Admin KONI.
                    @endif
                </p>
            </div>
            @if ($prefix === 'cabor')
                <a href="{{ route('cabor.surat.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#E60012] px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">+ Buat Surat</a>
            @endif
        </div>

        <form method="GET" class="mt-5 grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari perihal, nomor, atau cabang olahraga..." class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                <option value="">Semua status</option>
                @foreach (\App\Models\Surat::statusLabels() as $key => $label)
                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
                <a href="{{ route($routeBase.'.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-bold text-gray-600">Reset</a>
            </div>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Surat</th>
                        <th class="px-4 py-3">Cabang Olahraga</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        @php
                            $statusColor = match ($item->status) {
                                'diterima' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'revisi', 'perlu_revisi_staff' => 'bg-amber-100 text-amber-800',
                                'diteruskan_admin', 'menunggu_penyampaian' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-4">
                                <p class="font-bold text-gray-900">{{ $item->perihal }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ ucfirst($item->jenis_surat) }} · {{ $item->nomor_surat ?: 'Nomor belum diisi' }}</p>
                            </td>
                            <td class="px-4 py-4 text-gray-600">{{ $item->cabangOlahraga?->nama ?? 'KONI Kabupaten Klaten' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-gray-600">{{ $item->tanggal_surat?->format('d M Y') }}</td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusColor }}">{{ \App\Models\Surat::statusLabels()[$item->status] ?? $item->status }}</span></td>
                            <td class="px-4 py-4 text-right"><a href="{{ route($routeBase.'.show', $item) }}" class="font-bold text-red-600 hover:underline">Detail →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada surat yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $items->links() }}</div>
    </section>
</div>
@endsection
