<?php

namespace App\Http\Controllers\Api;

use App\Helpers\File;
use App\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ProfileController extends Controller
{
    public function currentUser(Request $request)
    {
        $user = $request->user();

        return Response::success(null, [
            'data' => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => isset($user->avatar) ? File::getUrl($user->avatar) : null
            ]
        ]);
    }

    public function update(Request $request)
    {
        switch ($request->type) {
            case 'name':
                $rules['value'] = ['required', 'string', 'max:100'];
                $update['name'] = $request->value;
                break;
            case 'username':
                $rules['value'] = ['required', 'string', 'max:20', 'unique:users,username,' . $request->user()->id];
                $update['username'] = Str::slug($request->value, '-');
                break;
            case 'avatar':
                $rules['value'] = ['required', 'file', 'image', 'max:5120'];
                break;
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::error($validator->errors()->first(), null, 422);
        }

        $user = $request->user();

        try {
            if ($request->type == 'avatar' && $request->hasFile('value')) {
                if (isset($user->avatar) && Storage::disk('s3')->exists($user->avatar)) {
                    File::delete($user->avatar);
                }
                $path = File::store($request->file('value'), "users/{$user->id}/avatar");
                $update['avatar'] = $path;
            }

            $user->update($update);

            return Response::success(null, [
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => isset($user->avatar) ? File::getUrl($user->avatar) : null
                ]
            ]);
        } catch (\Throwable $err) {
            Log::error('error AuthController update: ' . $err->getMessage());
            $statusCode = $err instanceof HttpExceptionInterface ? $err->getStatusCode() : 500;

            return Response::error($err->getMessage(), null, $statusCode);
        }
    }
}
