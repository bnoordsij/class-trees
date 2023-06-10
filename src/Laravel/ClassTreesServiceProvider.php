<?php

namespace Bnoordsij\ClassTrees\Laravel;

use Bnoordsij\ClassTrees\Console\Commands\ConvertQueuedClasses;
use Bnoordsij\ClassTrees\Console\Commands\CreateProject;
use Bnoordsij\ClassTrees\Models\Classe;
use Bnoordsij\ClassTrees\Observers\ClassObserver;
use Illuminate\Support\ServiceProvider;

class ClassTreesServiceProvider extends ServiceProvider
{
    private array $commands = [
        ConvertQueuedClasses::class,
        CreateProject::class,
    ];

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/class-trees.php', 'class-trees');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/projects.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'class-trees');

        $this->commands($this->commands);

        $this->publishes([
            __DIR__.'/../../resources/js' => resource_path('js/vendor/class-trees'),
        ], 'public');

        Classe::observe(ClassObserver::class);
    }
}
