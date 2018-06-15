<?php

namespace App\Http\Controllers\Auth;

use App\SocialAccount;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Mockery\Exception;

class SocialAccountController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $exception) {
            return redirect()->route('login');
        }

        $authUser = $this->findOrCreateUser($user, $provider);

        Auth::login($authUser, false);

        return redirect($this->redirectTo);
    }

    public function findOrCreateUser($socialUser, $provider)
    {
        $socialUser = SocialAccount::query()
                                   ->where('provider_name', $provider)
                                   ->where('provider_id', $socialUser->id)
                                   ->first();

        if ($socialUser) {
            return $socialUser->user;
        } else {
            $user = User::query()->where('email', $socialUser->email)->first();

            if ( ! $user) {
                User::query()->create([
                    'name'  => $socialUser->name,
                    'email' => $socialUser->email,
                ]);
            }

            $user->socialAccounts->create([
                'provider'    => $provider,
                'provider_id' => $socialUser->id,
            ]);

            return $user;
        }
    }
}
