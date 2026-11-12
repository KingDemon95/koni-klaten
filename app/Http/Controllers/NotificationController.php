<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $layout = match (true) {
            $user->hasRole('admin_koni') => 'layouts.admin',
            $user->hasRole('staff_koni') => 'layouts.staff',
            $user->hasRole('admin_cabor') => 'layouts.cabor',
            $user->hasRole('pelatih') => 'layouts.pelatih',
            default => 'layouts.atlet',
        };

        return view('notifications.index', [
            'layout' => $layout,
            'notifications' => $user->notifications()->latest()->paginate(20),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $item->markAsRead();
        $target = data_get($item->data, 'url');

        // Hanya izinkan path relatif lokal agar notifikasi tidak menjadi open redirect.
        if (! is_string($target)
            || ! Str::startsWith($target, '/')
            || Str::startsWith($target, '//')
            || Str::contains($target, ["\\", "\r", "\n"])) {
            $target = route('notifications.index');
        }

        return redirect()->to($target);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
