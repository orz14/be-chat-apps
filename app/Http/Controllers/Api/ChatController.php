<?php

namespace App\Http\Controllers\Api;

use App\Events\RoomEvent;
use App\Events\UserMessageReceivedEvent;
use App\Helpers\Response;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            ]))->toOthers();

            // TODO: optimize using queue
            $rooms = DB::table('chat_rooms as cr')
                ->join('rooms as r', 'r.id', 'cr.room_id')
                ->leftJoin('room_details as rd', 'rd.room_id', 'cr.room_id')
                ->where('cr.room_id', $request->room_id)
                ->get(['cr.user_id', 'r.type', 'rd.name']);

            foreach ($rooms as $room) {
                if ($room->user_id == $request->user()->id) continue; // kirim ke selain pengirim

                broadcast(new UserMessageReceivedEvent($room->user_id, [
                    'room_type' => $room->type,
                    'room_name' => $room->name ?? null,
                    'sender_id' => (int) $request->user()->id,
                    'sender_name' => $request->user()->name,
                    'sender_username' => $request->user()->username,
                    'type' => $request->type,
                    'content' => $request->content
                ]))->toOthers();
            }
            // TODO: optimize using queue

            return Response::success();
        } catch (\Throwable $err) {
            Log::error('error sendText: ' . $err->getMessage());
            $statusCode = $err instanceof HttpExceptionInterface ? $err->getStatusCode() : 500;

            return Response::error($err->getMessage(), null, $statusCode);
        }
    }
}
