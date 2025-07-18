<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function unread(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
                                   ->nonLu()
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $notification->update(['lu' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
                   ->where('lu', false)
                   ->update(['lu' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications marquées comme lues',
        ]);
    }
}
