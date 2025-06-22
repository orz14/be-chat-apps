<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Generate;
use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        $user = Socialite::driver($provider)->stateless()->user();

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
            Log::error('Error generating token: ' . $err->getMessage());

            return null;
        }
    }

    public function currentUser(Request $request)
    {
        $data = $request->user();

        return Response::success(null, [
            'data' => [
                'id' => $data->id,
                'name' => $data->name,
                'username' => $data->username,
                'email' => $data->email,
                'avatar' => $data->avatar
            ]
        ]);
    }
}
