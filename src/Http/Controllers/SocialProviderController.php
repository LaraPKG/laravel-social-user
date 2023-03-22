<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Larapkg\LaravelSocialUser\Http\Requests\SocialLoginRequest;
use Larapkg\LaravelSocialUser\Models\UserSocialProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\AbstractUser;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

use function config;
use function response;

class SocialProviderController extends Controller
{
    private string $userModel;

    private const INVALID_PROVIDER = 'Invalid provider';

    public function __construct()
    {
        $this->userModel = config('laravel-social-user.user_model');
    }

    public function socialLogin(SocialLoginRequest $request, string $provider): JsonResponse
    {
        try {
            $socialite = Socialite::driver($provider);
        } catch (Throwable) {
            throw new BadRequestException(self::INVALID_PROVIDER);
        }

        $socialProviderUser = $socialite?->userFromToken($request->get('access_token'));

        if (!$socialProviderUser) {
            throw new BadRequestException(self::INVALID_PROVIDER);
        }

        $user = $this->userModel::updateOrCreate([
            'name' => $socialProviderUser->getName(),
            'email' => $socialProviderUser->getEmail(),
        ]);

        $this->handleUserProviderRecord($user, $socialProviderUser, $provider);

        $token = $user->createToken(config('app.name'))->accessToken;

        return response()->json([
            '_token' => $token
        ]);
    }
    
    protected function handleUserProviderRecord(
        Model $user,
        AbstractUser $socialProviderUser,
        string $provider,
    ): void {
        $attributes = [
            'user_id' => $user->getAttribute('id'),
            'provider' => $provider,
        ];

        $values = [
            'provider_id' => $socialProviderUser->getId(),
            'email' => $socialProviderUser->getEmail(),
            'avatar' => $socialProviderUser->getAvatar(),
        ];

        UserSocialProvider::query()->updateOrCreate($attributes, $values);
    }
}
