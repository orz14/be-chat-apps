<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function personal(Request $request)
    {
        $user_id = $request->user()->id;
        $rooms = DB::table('chat_rooms as cr1')
            ->join('rooms as r', 'r.id', 'cr1.room_id')
            ->join('chat_rooms as cr2', function ($join) use ($user_id) {
                $join->on('cr2.room_id', 'cr1.room_id')
                    ->where('cr2.user_id', '!=', $user_id);
            })
            ->join('users as u', 'u.id', 'cr2.user_id')
            ->where('cr1.user_id', $user_id)
            ->where('r.type', 'personal')
            ->get([
                'r.type as room_type',
                'cr1.room_id',
                'u.name as room_name',
                'u.avatar as room_picture',
                'u.id as user_id'
            ]);

        return Response::success(null, ['rooms' => $rooms]);
    }

    public function group(Request $request)
    {
        $user_id = $request->user()->id;
        $rooms = DB::table('chat_rooms as cr')
            ->join('rooms as r', 'r.id', 'cr.room_id')
            ->join('room_details as rd', 'rd.room_id', 'r.id')
            ->where('cr.user_id', $user_id)
            ->where('r.type', 'group')
            ->get([
                'r.type as room_type',
                'cr.room_id',
                'rd.name as room_name',
                'rd.display_picture as room_picture'
            ]);

        return Response::success(null, ['rooms' => $rooms]);
    }
}
