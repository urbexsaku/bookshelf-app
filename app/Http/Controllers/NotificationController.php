<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * 通知一覧画面を表示する。
     *
     * @return View 通知一覧画面のビュー
     */
    public function index(): View
    {
        $notifications = auth()->user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知を既読にする。
     *
     * @param  string  $notification_id  既読にする通知のID
     * @return RedirectResponse 通知一覧画面へのリダイレクト
     */
    public function read(string $notification_id): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($notification_id);

        $notification->markAsRead();

        return redirect()->route('notifications.index');
    }
}
