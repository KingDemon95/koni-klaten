@extends($layout)

@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <section class="rounded-2xl bg-gradient-to-r from-[#171717] to-[#43151b] p-6 text-white shadow-sm sm:p-8">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[#FFD700]">Pusat Informasi</p>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black sm:text-3xl">Notifikasi Saya</h2>
                <p class="mt-2 max-w-2xl text-sm text-white/80">Pemberitahuan perubahan status prestasi, izin latihan, agenda, dan persuratan yang berkaitan dengan akun Anda.</p>
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3">
                <p class="text-2xl font-black">{{ $unreadCount }}</p>
                <p class="text-xs text-white/75">Belum dibaca</p>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-black text-gray-900">Riwayat pemberitahuan</h3>
            <p class="text-sm text-gray-500">Notifikasi hanya dapat dilihat oleh pemilik akun.</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 transition hover:border-[#E60012] hover:text-[#E60012]">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            @php($data = $notification->data ?? [])
            <article class="flex flex-col gap-4 rounded-2xl border {{ $notification->read_at ? 'border-gray-200 bg-white' : 'border-red-200 bg-red-50/60' }} p-4 shadow-sm sm:flex-row sm:items-start sm:justify-between sm:p-5">
                <div class="flex min-w-0 gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $notification->read_at ? 'bg-gray-100 text-gray-500' : 'bg-red-100 text-[#E60012]' }}" aria-hidden="true">
                        @switch($data['category'] ?? 'info')
                            @case('prestasi') 🏅 @break
                            @case('izin-latihan') 📄 @break
                            @case('persuratan') ✉️ @break
                            @case('agenda') 📅 @break
                            @default 🔔
                        @endswitch
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="font-black text-gray-900">{{ $data['title'] ?? 'Pemberitahuan' }}</h4>
                            @unless($notification->read_at)
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-red-700">Baru</span>
                            @endunless
                        </div>
                        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $data['message'] ?? '' }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('notifications.open', $notification->id) }}" class="shrink-0 sm:self-center">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-[#171717] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#E60012] sm:w-auto">
                        Buka pemberitahuan
                    </button>
                </form>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                <div class="text-3xl" aria-hidden="true">🔔</div>
                <h4 class="mt-3 font-black text-gray-900">Belum ada notifikasi</h4>
                <p class="mt-1 text-sm text-gray-500">Pemberitahuan aktivitas akan muncul di halaman ini.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
