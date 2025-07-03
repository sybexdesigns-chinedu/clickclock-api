<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = request()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($notifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $notification = Notification::create([
            'user_id' => $request->user()->id,
            'action' => 'post',
            'message' => $request->message
        ]);
        return response()->json($notification, 201);
    }

    public function show(Notification $notification)
    {
        $notification->markAsRead();
        return response()->json($notification);
    }

    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();
        return response()->json(['message' => 'Notifications marked as read']);
    }

    public function markAllAsRead()
    {
        request()->user()->notifications()->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function delete(Notification $notification)
    {
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
