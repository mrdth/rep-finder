<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\BattlenetAuthRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialiteBattlenetController extends Controller
{
    public function create(BattlenetAuthRequest $request): RedirectResponse
    {
        // Set the region for battlenet Oauth requests based on teh selected region
        // from the log in form
        config('services.battlenet.region', $request->region);

        // Because we're passing a 'state' param with the request,
        // we need to handle some state management ourselves,
        // as 'state' is used by Socialite internally
        $state = "bnet{$request->region}-" . Str::random(40);
        $request->session()->put('bnetstate', $state);

        return Socialite::driver('battlenet')
            ->scopes(['wow.profile'])
            ->with(['state' => $state])
            ->redirect();
    }

    public function store(Request $request): RedirectResponse
    {
        // Manually check the returned state matches the one placed into session
        throw_if(
            $request->session()->pull('bnetstate') !== $request->state,
            new InvalidStateException()
        );

        $region = Str::remove('bnet', explode('-', $request->state)[0]);

        $socialite_user = Socialite::driver('battlenet')
            ->stateless()
            ->user();

        $user = User::updateOrCreate([
            'external_id' => $socialite_user->id,
            'name' => $socialite_user->nickname,
            'region' => $region,
            'token' => $socialite_user->token,
            'token_expires_at' => Carbon::now()->addSeconds($socialite_user->expiresIn),
        ]);

        Auth::login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
