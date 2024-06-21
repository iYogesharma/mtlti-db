<?php 

namespace YS\MultiDB;

use Illuminate\Support\ServiceProvider;
use YS\MultiDB\Commands\MakeNewMigration;
use YS\MultiDB\Commands\NewMigrations;
use YS\MultiDB\Commands\NewMigrationsRollback;
use YS\MultiDB\Commands\NewSeeder;

class MultiDbServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishesMigrations([
            __DIR__.'/database/migrations' => database_path('migrations'),
        ], 'multidb:migrations');

        $this->publishes([
            __DIR__.'/Models/Client.php' => app_path('Models/Client.php'),
        ], 'multidb:models');


        $this->publishes([
            __DIR__.'/config/multidb.php' => config_path('multidb.php')
        ],'multidb:config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                NewMigrations::class,
                NewMigrationsRollback::class,
                MakeNewMigration::class,
                NewSeeder::class,
            ]);
        }
        
    }


     /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $multidbConfig=__DIR__.'/config/multidb.php';

        // merge config
        $this->mergeConfigFrom($multidbConfig, 'multidb');


        $this->app->singleton('tr', function () {
            return new TransactionManager();
        });

    }

}