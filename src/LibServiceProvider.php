<?php

namespace Hanoivip\Game;

use Illuminate\Support\ServiceProvider;

class LibServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/game.php' => config_path('game.php'),
            __DIR__.'/../views' => resource_path('views/vendor/hanoivip'),
            __DIR__.'/../lang' => resource_path('lang/vendor/hanoivip'),
        ], 'config');
        $this->publishes([
            __DIR__.'/../resources/assets/js' => public_path('js'),
        ], 'assets');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../views', 'hanoivip');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadTranslationsFrom( __DIR__.'/../lang', 'hanoivip.game');
    }

    public function register()
    {
        $this->app->bind('GameHelper', \Hanoivip\Game\Services\GameHelper::class);
        $this->app->bind('ServerService', \Hanoivip\Game\Services\ServerService::class);
        $this->mergeConfigFrom(__DIR__.'/../config/game.php', 'game');
    }
}
