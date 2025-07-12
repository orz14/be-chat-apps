<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Helpers\Generate;
use App\Helpers\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class AuthController extends Controller
{
    public function redirectToProvider($provider, SocialiteFactory $socialite)
    {
        /** @var AbstractProvider $driver */
        $driver = $socialite->driver($provider);
        return $driver->stateless()->redirect();
    }

    public function handleProviderCallback($provider, SocialiteFactory $socialite)
    {
        /** @var AbstractProvider $driver */
        $driver = $socialite->driver($provider);
        $user = $driver->stateless()->user();

        $authUser = $this->findOrCreateUser($user, $provider);

        if (!$authUser) {
            return redirect(config('app.frontend_url') . '/auth/callback?status=failed');
        }

        $token = $this->generateToken($authUser);

        if (!$token) {
            return redirect(config('app.frontend_url') . '/auth/callback?status=failed');
        }

        return redirect(config('app.frontend_url') . '/auth/callback?token=' . $token);
    }

    private function findOrCreateUser($user, $provider)
    {
        $field = strtolower($provider) . '_id';
        $authUser = User::where($field, $user->getId())->first();
        if ($authUser) {
            return $authUser;
        }

        if ($user->getEmail() != null) {
            $usermail = User::where('email', $user->getEmail())->first();
            if ($usermail) {
                $usermail->update([
                    $field => $user->getId()
                ]);

                return $usermail;
            } else {
                $user = User::create([
                    'name' => $user->getName() ?? 'User',
                    'username' => 'user-' . Generate::randomString(10),
                    'email' => $user->getEmail(),
                    $field => $user->getId(),
                ]);

                event(new Registered($user));

                return $user;
            }
        } else {
            return null;
        }
    }

    private function generateToken($user): string|null
    {
        try {
            $token_name = Generate::randomString(32);
            $expiresAt = null;
            $token = $user->createToken($token_name, ["*"], $expiresAt)->plainTextToken;

            return (string) $token;
        } catch (\Throwable $err) {
            Log::error('error AuthController generateToken: ' . $err->getMessage());

            return null;
        }
    }

    public function currentUser(Request $request)
    {
        $user = $request->user();

        return Response::success(null, [
            'data' => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar
            ]
        ]);
    }

    public function update(Request $request)
    {
        switch ($request->type) {
            case 'name':
                $update = [
                    'name' => $request->value
                ];
                break;
            case 'username':
                $update = [
                    'username' => Str::slug($request->value, '-')
                ];
                break;
        }

        $user = $request->user();

        try {
            $user->update($update);

            return Response::success(null, [
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar
                ]
            ]);
        } catch (\Throwable $err) {
            Log::error('error AuthController update: ' . $err->getMessage());
            $statusCode = $err instanceof HttpExceptionInterface ? $err->getStatusCode() : 500;

            return Response::error($err->getMessage(), null, $statusCode);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return Response::success();
        } catch (\Throwable $err) {
            Log::error('error AuthController logout: ' . $err->getMessage());
            $statusCode = $err instanceof HttpExceptionInterface ? $err->getStatusCode() : 500;

            return Response::error($err->getMessage(), null, $statusCode);
        }
    }
}
