<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    /**
     * Mark all of the current user's unread notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notifications marked as read.');
    }
}