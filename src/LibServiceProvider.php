<?php

namespace Hanoivip\Game;

use Illuminate\Support\ServiceProvider;
use Hanoivip\Game\Contracts\IPayment;

class LibServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/game.php' => config_path('game.php')
        ]);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../views', 'hanoivip');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('GameService', \Hanoivip\Game\Services\GameService::class);
        $this->app->bind(IPayment::class, \Hanoivip\Game\Services\FakePayment::class);
    }
}
