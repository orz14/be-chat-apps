<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomEvent;
use App\Helpers\Response;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

    public function sendText(Request $request)
    {
        try {
            $sentAt = Carbon::now()->toDateTimeString();

            DB::table('chat_messages')->insert([
                'id' => $request->id,
                'room_id' => $request->room_id,
                'sender_id' => $request->user()->id,
                'type' => $request->type,
                'content' => $request->content,
                'sent_at' => $sentAt
            ]);

            broadcast(new RoomEvent($request->room_id, [
                'id' => $request->id,
                'sender_id' => $request->user()->id,
                'content' => $request->content,
                'sent_at' => $sentAt
            ]));

            return Response::success();
        } catch (\Throwable $err) {
            $statusCode = $err instanceof HttpExceptionInterface ? $err->getStatusCode() : 500;

            return Response::error($err->getMessage(), null, $statusCode);
        }
    }
}
