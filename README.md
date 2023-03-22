# Laravel Social User

Laravel Social User is a package that allows you to easily add social login to your Laravel API.

Combined with [Laravel Socialite](https://laravel.com/docs/10.x/socialite), [Laravel Socialite Providers](https://socialiteproviders.netlify.app/) 
and [Laravel Passport](https://laravel.com/docs/10.x/passport), you can easily add social login to your API for many social login providers.

## Installation

You can install the package via composer:

```bash
composer require larapkg/laravel-social-user
```

To publish the configuration file which enables the setting of your applications user model, run:

```bash
php artisan vendor:publish --provider="Larapkg\LaravelSocialUser\Providers\LaravelSocialProviderServiceProvider" --tag="laravel-social-user-config"
```

If you want to alter the migrations, you can publish them with:

```bash
php artisan vendor:publish --provider="Larapkg\LaravelSocialUser\Providers\LaravelSocialProviderServiceProvider" --tag="laravel-social-user-migrations"
```

## Usage

Run the migration to create the `user_social_providers` table.

Then attach the `HasSocialUsers` trait to your user model.

Publish the config and set up your required details, ensuring you set the user model.

Add a social login button to your frontend application and use the social provider's SDK to get the user's access token. 
Then send a POST request to your API with the access token and the provider name.

`/social-login/[provider-name]` passing the `access_token` in the request body.

## Considerations

So every application needs a user strategy, and this package does not provide one.

Well it does, but it's not necessarily a good one or suitable for your use case.

So do you ONLY want social login, so your not responsible for user accounts?, or do you want to manage user accounts yourself?
From a [GDPR](https://gdpr-info.eu/) point of view etc., unless you know what you are doing, you should probably use the social login only strategy,
and avoid storing any user data yourself that is not necessary. And provide a way for users to delete their accounts and maybe
consider storing last_login details and deleting accounts after a period of inactivity (good practice to email users before).

As such, this package stores only the social provider's user id, name, email address and avatar, and when a user is deleted,
if you have attached the `HasSocialUsers` trait to your user model, it will also delete the social user records for that user.

When using this package, when passing the social providers `access_token` to your API, it will always create a new user
unless the social provider account has the same name and email as an existing user. i.e. on social login of a previously
used social login account, the same user will be used, but social login from a different social login account will create
a new user and those accounts will not be linked in any way.

## Other Considerations

Socialite and Passport should be installed, your social providers setup and your user model setup to use passport.

Those things are out of scope for this readme, but you can find more information on those things in the links below.

 - [Laravel Socialite](https://laravel.com/docs/10.x/socialite)
 - [Laravel Socialite Providers](https://socialiteproviders.netlify.app/)
 - [Laravel Passport](https://laravel.com/docs/10.x/passport)
