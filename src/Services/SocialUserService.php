<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Services;

use Illuminate\Database\Eloquent\Model;
use Larapkg\LaravelSocialUser\Models\UserSocialProvider;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
class SocialUserService
{
    private readonly string $userModel;

    public function __construct()
    {
        $this->userModel = config('laravel-social-user.user_model');
    }

    public function getToken(string $provider, string $accessToken): string
    {
        $driver = $this->getDriver($provider);

        $socialUser = $this->getSocialUser($driver, $accessToken);

        $systemUser = $this->createUser($socialUser);

        $this->createSocialUserRecord($systemUser, $socialUser, $provider);

        return $systemUser->createToken(config('app.name'))->accessToken;
    }

    public function getDriver(string $provider): Provider
    {
        return Socialite::driver($provider);
    }

    public function getSocialUser(Provider $driver, string $token): AbstractUser
    {
        return $driver->userFromToken($token);
    }

    public function createUser(AbstractUser $socialUser): Model
    {
        return $this->userModel::updateOrCreate([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
        ]);
    }

    public function createSocialUserRecord(Model $systemUser, AbstractUser $socialUser, string $provider): void
    {
        $attributes = [
            'user_id' => $systemUser->getKey(),
            'provider' => $provider,
        ];

        $values = [
            'provider_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ];

        UserSocialProvider::query()->updateOrCreate($attributes, $values);
    }
}
