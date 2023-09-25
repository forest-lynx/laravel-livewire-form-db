<?php

declare(strict_types=1);

namespace ForestLynx\FormDB\Providers;

use ForestLynx\FormDB\Console\Commands\FormDBCommand;
use ForestLynx\FormDB\Exceptions\UnsupportedDbDriver;
use ForestLynx\FormDB\Schema\Contracts\SchemaContract;
use ForestLynx\FormDB\Schema\SchemaMysql;
use Illuminate\Support\ServiceProvider;

class PackagesServiceProvider extends ServiceProvider
{
    protected $namespace = "formdb";

    public function register()
    {
        parent::register();
    }

    public function boot()
    {
        /* Add config file */
        $this->publishes([
            __DIR__ . "/../../config/{$this->namespace}.php" => \config_path("{$this->namespace}.php"),
        ], 'formdb-config');

        $this->app->bind(SchemaContract::class, function ($app, $parameters) {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            $class = match ($driver) {
                'mysql' => SchemaMysql::class,
                /*'sqlite' => '',
                'pgsql' => '',*/
                default => throw UnsupportedDbDriver::create($driver)
            };

            return new $class(...$parameters);
        }, true);

        if ($this->app->runningInConsole()) {
            $this->commands([
                FormDBCommand::class,
            ]);
        }
    }
}
