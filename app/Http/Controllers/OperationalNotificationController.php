<?php

namespace App\Http\Controllers;

use App\Services\OperationalNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationalNotificationController extends Controller
{
    public function open(
        Request $request,
        string $key,
        OperationalNotificationService $notifications
    ): RedirectResponse {
        $route = $notifications->markRead($request->user(), $key);

        abort_unless($route, 404);

        return redirect()->route($route);
    }

    public function markAll(
        Request $request,
        OperationalNotificationService $notifications
    ): RedirectResponse {
        $notifications->markAllRead($request->user());

        return back()->with('success', 'Semua notifikasi operasional ditandai sudah dibaca.');
    }
}
