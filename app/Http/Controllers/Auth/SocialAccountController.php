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

    public function findOrCreateUser($user, $provider)
    {
        $user = SocialAccount::query()->findOrNew($user->id, $provider);

        return $user;
    }
}
