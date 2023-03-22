<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'access_token' => 'required|string',
        ];
    }
}
