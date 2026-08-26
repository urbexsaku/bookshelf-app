<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    public function read($notification_id): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($notification_id);

        $notification->markAsRead();

        return redirect()->route('notifications.index');
    }
}
