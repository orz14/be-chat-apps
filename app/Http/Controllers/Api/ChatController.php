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
    public function loadChats($roomId, $lastSentAt = null, $lastMessageId = null)
    {
        $chats = DB::table('chat_messages as cm')
            ->join('users as u', 'u.id', 'cm.sender_id')
            ->where('cm.room_id', $roomId)
            ->when(!empty($lastSentAt) && !empty($lastMessageId), function ($q) use ($lastSentAt, $lastMessageId) {
                $q->where(function ($q1) use ($lastSentAt, $lastMessageId) {
                    $q1->where('cm.sent_at', '<', $lastSentAt)
                        ->orWhere(function ($q2) use ($lastSentAt, $lastMessageId) {
                            $q2->where('cm.sent_at', '=', $lastSentAt)
                                ->where('cm.id', '<', $lastMessageId);
                        });
                });
            })
            ->orderBy('cm.sent_at', 'desc')
            ->orderBy('cm.id', 'desc')
            ->limit(20)
            ->get(['cm.*', 'u.username as sender_username'])
            ->reverse()
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'room_id' => $item->room_id,
                    'sender_id' => (int) $item->sender_id,
                    'sender_username' => $item->sender_username,
                    'type' => $item->type,
                    'content' => $item->content,
                    'sent_at' => $item->sent_at
                ];
            });

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
                'room_id' => $request->room_id,
                'sender_id' => (int) $request->user()->id,
                'sender_name' => $request->user()->name,
                'sender_username' => $request->user()->username,
                'type' => $request->type,
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
