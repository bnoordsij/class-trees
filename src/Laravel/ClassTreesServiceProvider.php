<?php

namespace App\Providers;

use App\Models\Classe;
use App\Observers\ClassObserver;
use Illuminate\Support\ServiceProvider;

class ClassTreesServiceProvider extends ServiceProvider
{
    private array $commands = [
        \Bnoordhuis\ClassTrees\Console\Commands\ClassTreeCommand::class,
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/class-trees.php', 'class-trees');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/class-trees.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->commands($this->commands);
    }

    public function boot()
    {
        Classe::observe(ClassObserver::class);
    }
}
