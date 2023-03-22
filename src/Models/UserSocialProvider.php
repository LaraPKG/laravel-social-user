<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 *
 * @property int $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string $email
 * @property string $token
 * @property string $refresh_token
 * @property int $expires_in
 * @property string $token_secret
 * @property string $avatar
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserSocialProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'email',
        'token',
        'refresh_token',
        'expires_in',
        'token_secret',
        'avatar',
    ];

    protected $casts = [
        'expires_in' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('laravel-social-provider.user_model')::class);
    }
}
