<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Http\Controllers;

use Illuminate\Routing\Controller;
use Larapkg\LaravelSocialUser\Http\Requests\SocialLoginRequest;
use Larapkg\LaravelSocialUser\Services\SocialUserService;
use Symfony\Component\HttpFoundation\JsonResponse;

use function response;

class SocialProviderController extends Controller
{
    public function __construct(private readonly SocialUserService $socialUserService)
    {
    }

    public function socialLogin(SocialLoginRequest $request, string $provider): JsonResponse
    {
        return response()->json([
            '_token' => $this->socialUserService->getToken($provider, $request->get('access_token')),
        ]);
    }
}
