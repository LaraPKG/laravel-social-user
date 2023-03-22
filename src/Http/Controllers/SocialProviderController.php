<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Larapkg\LaravelSocialUser\Models\UserSocialProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\One\User as OauthOneUser;
use Laravel\Socialite\Two\User as OauthTwoUser;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class SocialProviderController extends Controller
{
    private string $userModel;

    public function __construct()
    {
        $this->userModel = config('laravel-social-user.user_model');
    }

    public function redirect(string $provider): RedirectResponse
    {
        try {
            $redirect = Socialite::driver($provider)->redirect()->getTargetUrl();
        } catch (Throwable) {
            throw new BadRequestException('Invalid provider');
        }
        
        return redirect($redirect);
    }

    public function callback(string $provider): RedirectResponse
    {
        $socialProviderUser = Socialite::driver($provider)->user();

        $user = $this->userModel::updateOrCreate([
                'name' => $socialProviderUser->getName(),
                'active_provider' => $provider,
                'active' => true,
        ]);
        
        $userSocialProvider = $this->createUserProviderRecord($user, $socialProviderUser, $provider);
        
        $user->givePermissionTo($this->userModel::defaultPermissions());
        
        if (!$user->active || !$userSocialProvider->active) {
            throw new AccessDeniedException('Unauthorized');
        }

        Auth::login($user);
        
        return redirect()->intended();
    }

    public function activate(UserSocialProvider $provider): RedirectResponse
    {
        $provider->active = !$provider->active;
        $provider->save();

        return redirect()->back();
    }
    
    private function createUserProviderRecord(
        Model $user,
        SocialUser $socialProviderUser,
        string $provider,
    ): ?UserSocialProvider {
        if ($socialProviderUser instanceof OauthOneUser) {
            return $this->createOauthOneProviderRecord($user, $socialProviderUser, $provider);
        }
        
        if (!($socialProviderUser instanceof OauthTwoUser)) {
            return null;
        }
        
        return $this->createOauthTwoProviderRecord($user, $socialProviderUser, $provider);
    }
    
    private function createOauthOneProviderRecord(
        Model $user,
        OauthOneUser $socialProviderUser,
        string $provider,
    ): UserSocialProvider {
        $attributes = [
            'user_id' => $user->getAttribute('id'),
            'provider_id' => $socialProviderUser->getId(),
        ];

        $values = [
            'provider' => $provider,
            'email' => $socialProviderUser->getEmail(),
            'avatar' => $socialProviderUser->getAvatar(),
            'token' => $socialProviderUser->token,
            'token_secret' => $socialProviderUser->tokenSecret,
            'active' => true,
        ];

        return $this->createRecord($attributes, $values);
    }
    
    private function createOauthTwoProviderRecord(
        Model $user,
        OauthTwoUser $socialProviderUser,
        string $provider,
    ): UserSocialProvider {
        $attributes = [
            'user_id' => $user->getAttribute('id'),
            'provider_id' => $socialProviderUser->getId(),
        ];

        $values = [
            'provider' => $provider,
            'email' => $socialProviderUser->getEmail(),
            'avatar' => $socialProviderUser->getAvatar(),
            'token' => $socialProviderUser->token,
            'refresh_token' => $socialProviderUser->refreshToken,
            'expires_in' => $socialProviderUser->expiresIn,
            'active' => true,
        ];
        
        return $this->createRecord($attributes, $values);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     */
    private function createRecord(array $attributes, array $values): UserSocialProvider
    {
        /** @var UserSocialProvider $userSocialProvider */
        $userSocialProvider = UserSocialProvider::query()->updateOrCreate($attributes, $values);
        
        return $userSocialProvider;
    }
}
