<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function loadChats(Request $request, $roomId, $lastSentAt = null, $lastMessageId = null)
    {
        $check = DB::table('chat_rooms')->where('room_id', $roomId)->where('user_id', $request->user()->id)->exists();
        if (!$check) {
            return Response::error('Anda tidak memiliki akses.', null, 403);
        }

        $chats = DB::table('chat_messages')
            ->where('room_id', $roomId)
            ->when(!empty($lastSentAt) && !empty($lastMessageId), function ($q) use ($lastSentAt, $lastMessageId) {
                $q->where(function ($query) use ($lastSentAt, $lastMessageId) {
                    $query->where('sent_at', '<', $lastSentAt)
                        ->orWhere(function ($query2) use ($lastSentAt, $lastMessageId) {
                            $query2->where('sent_at', '=', $lastSentAt)
                                ->where('id', '<', $lastMessageId);
                        });
                });
            })
            ->orderBy('sent_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return Response::success(null, ['chats' => $chats]);
    }
}
