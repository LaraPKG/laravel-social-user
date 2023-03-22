<?php

declare(strict_types=1);

namespace Larapkg\LaravelSocialUser\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Larapkg\LaravelSocialUser\Models\UserSocialProvider;

trait HasSocialUser
{
    public static function bootHasSocialUser(): void
    {
        static::deleting(static fn ($model) => $model->socialProviders()->delete());
    }

    public function socialProviders(): HasMany
    {
        return $this->hasMany(UserSocialProvider::class);
    }
}
