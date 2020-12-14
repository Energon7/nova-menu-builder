<?php

namespace Energon7\MenuBuilder;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Energon7\MenuBuilder\Http\Middleware\Authorize;
use Energon7\MenuBuilder\Http\Resources\MenuResource;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class MenuBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'menu-builder');

        $this->app->booted(function () {
            $this->routes();
        });

        $this->publishMigrations();

        $this->publishConfig();

        Nova::serving(function (ServingNova $event) {
            //
        });

        Nova::resources([
            MenuResource::class,
        ]);
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->namespace('Energon7\MenuBuilder\Http\Controllers')
            ->prefix('nova-vendor/menu-builder')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Publish required migration
     */
    private function publishMigrations()
    {
        $this->publishes([
            __DIR__.'/Migrations/create_menus_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_menus_table.php'),
            ], 'menu-builder-migration');

    }

    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/menu-builder.php' => config_path('menu-builder.php'),
        ], 'config');
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
