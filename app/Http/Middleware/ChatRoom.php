<?php

namespace App\Http\Middleware;

use App\Helpers\Response;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ChatRoom
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $roomId = $request->route('roomId');

        $exists = DB::table('chat_rooms')->where('room_id', $roomId)->where('user_id', $request->user()->id)->exists();
        if (!$exists) {
            return Response::error('Anda tidak memiliki akses.', null, 403);
        }

        return $next($request);
    }
}
