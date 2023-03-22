<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Larapkg\LaravelSocialUser\Http\Requests\SocialLoginRequest;
use Larapkg\LaravelSocialUser\Models\UserSocialProvider;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\One\User as OauthOneUser;
use Laravel\Socialite\Two\User as OauthTwoUser;
use Spatie\RouteAttributes\Attributes\Post;
use Symfony\Component\Finder\Exception\AccessDeniedException;
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

        $socialProviderUser = $this->createUserProviderRecord($user, $socialProviderUser, $provider);

        if (!$socialProviderUser) {
            throw new AccessDeniedException(self::INVALID_PROVIDER);
        }

        $token = $user->createToken(config('app.name'))->accessToken;

        return response()->json([
            '_token' => $token
        ]);
    }
    
    protected function createUserProviderRecord(
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
    
    protected function createOauthOneProviderRecord(
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
        ];

        return $this->createRecord($attributes, $values);
    }
    
    protected function createOauthTwoProviderRecord(
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
        ];
        
        return $this->createRecord($attributes, $values);
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $values
     */
    protected function createRecord(array $attributes, array $values): UserSocialProvider
    {
        /** @var UserSocialProvider $userSocialProvider */
        $userSocialProvider = UserSocialProvider::query()->updateOrCreate($attributes, $values);
        
        return $userSocialProvider;
    }
}
