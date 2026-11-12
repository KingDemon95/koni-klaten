@php($notificationUnreadCount = auth()->user()->unreadNotifications()->count())
<a
    href="{{ route('notifications.index') }}"
    aria-label="Notifikasi{{ $notificationUnreadCount > 0 ? ', '.$notificationUnreadCount.' belum dibaca' : '' }}"
    class="relative inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-700 transition hover:border-[#E60012] hover:bg-red-50 hover:text-[#E60012]"
>
    <span aria-hidden="true" class="text-base">🔔</span>
    <span class="hidden sm:inline">Notifikasi</span>
    @if($notificationUnreadCount > 0)
        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-[#E60012] px-1.5 py-0.5 text-[10px] font-black text-white">
            {{ $notificationUnreadCount > 99 ? '99+' : $notificationUnreadCount }}
        </span>
    @endif
</a>
