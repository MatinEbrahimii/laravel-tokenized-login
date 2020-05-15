<?php

namespace MatinEbrahimi\TokenizedLogin;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MatinEbrahimi\TokenizedLogin\Authenticator\SessionAuth;
use MatinEbrahimi\TokenizedLogin\Facades\AuthFacade;
use MatinEbrahimi\TokenizedLogin\Facades\TokenGeneratorFacade;
use MatinEbrahimi\TokenizedLogin\Facades\TokenSenderFacade;
use MatinEbrahimi\TokenizedLogin\Facades\TokenStoreFacade;
use MatinEbrahimi\TokenizedLogin\Facades\UserProviderFacade;
use MatinEbrahimi\TokenizedLogin\Http\ResponderFacade;
use MatinEbrahimi\TokenizedLogin\TokenGenerators\FakeTokenGenerator;
use MatinEbrahimi\TokenizedLogin\TokenStore\FakeTokenStore;
use MatinEbrahimi\TokenizedLogin\TokenStore\TokenStore;

class TwoFactorAuthServiceProvider extends ServiceProvider
{
    private $namespace = 'MatinEbrahimi\TokenizedLogin\Http\Controllers';

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/tokenized_login.php', 'tokenized_login');
        AuthFacade::shouldProxyTo(SessionAuth::class);
        UserProviderFacade::shouldProxyTo(UserProvider::class);
        if (app()->runningUnitTests()) {
            $tokenGenerator = FakeTokenGenerator::class;
            $tokenStore = FakeTokenStore::class;
            $tokenSender = FakeTokenSender::class;
        } else {
            $tokenSender = config('tokenized_login.token_sender');
            $tokenGenerator = config('tokenized_login.token_generator');
            $tokenStore = TokenStore::class;
        }
        ResponderFacade::shouldProxyTo(config('tokenized_login.responses'));
        TokenGeneratorFacade::shouldProxyTo($tokenGenerator);
        TokenStoreFacade::shouldProxyTo($tokenStore);
        TokenSenderFacade::shouldProxyTo($tokenSender);
    }

    public function boot()
    {
        if (! $this->app->routesAreCached() && config('tokenized_login.use_default_routes')) {
            $this->defineRoutes();
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config' => $this->app->configPath(),
            ], 'tokenized_login');
        }
    }

    private function defineRoutes()
    {
        Route::middleware(config('tokenized_login.route_middlewares'))
            ->namespace($this->namespace)
            ->group(__DIR__.'/routes.php');
    }
}
