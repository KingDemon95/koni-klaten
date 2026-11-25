@extends($layout)

@section('title', 'Pesan Persuratan')
@section('heading', 'Pesan Persuratan')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#43151b] p-6 text-white shadow-sm sm:p-8">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#FFD700]">Pusat Komunikasi</p>
        <h2 class="mt-2 text-2xl font-black sm:text-3xl">Pesan Persuratan</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">
            Disposisi Staff KONI, komentar Admin Cabor, dan dokumen balasan tercatat berdasarkan surat terkait.
        </p>
    </section>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse($messages as $message)
            <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-red-600">{{ $message->typeLabel() }}</p>
                        <h3 class="mt-1 break-words text-base font-black text-gray-900">{{ $message->surat?->perihal ?? 'Surat tidak tersedia' }}</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $message->surat?->cabangOlahraga?->nama ?? 'KONI Kabupaten Klaten' }}
                            · {{ $message->sender?->name ?? 'Pengguna tidak tersedia' }}
                            · {{ $message->senderRoleLabel() }}
                        </p>
                    </div>
                    <time class="shrink-0 text-xs text-gray-400">{{ $message->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</time>
                </div>

                @if($message->body)
                    <p class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $message->body }}</p>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route($routeBase.'.show', $message->surat) }}" class="inline-flex items-center rounded-lg bg-[#171717] px-4 py-2.5 text-xs font-bold text-white hover:bg-[#E60012]">
                        Buka percakapan surat
                    </a>
                    @if($message->attachment_path)
                        <a href="{{ route($routeBase.'.pesan.file', [$message->surat, $message]) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2.5 text-xs font-bold text-gray-700 hover:border-red-300 hover:text-red-700">
                            Unduh {{ $message->attachment_original_name ?: 'lampiran' }}
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                <div class="text-3xl" aria-hidden="true">✉️</div>
                <h3 class="mt-3 font-black text-gray-900">Belum ada pesan persuratan</h3>
                <p class="mt-1 text-sm text-gray-500">Pesan disposisi dan balasan akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </div>

    @if($messages->hasPages())
        <div>{{ $messages->links() }}</div>
    @endif
</div>
@endsection
