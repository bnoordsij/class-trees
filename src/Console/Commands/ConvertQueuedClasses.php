<?php

namespace Bnoordsij\ClassTrees\Console\Commands;

use Bnoordsij\ClassTrees\Models\QueuedClass;
use Bnoordsij\ClassTrees\Services\ClassConverter;
use Illuminate\Console\Command;
use Bnoordsij\ClassTrees\Models\Project;
use Illuminate\Support\Collection;

class ConvertQueuedClasses extends Command
{
    protected $signature = 'app:convert-queued-classes
                            {--p|project-id= : Provide a project ID (if no ID is provided we\'ll return a list of projects)}
                            {--class-distance= :
                            {--repeat=1 : Number of times you want to run this command sequentially}';

    protected $description = 'Convert all queued classes for a project';

    public function handle(): int
    {
        if (Project::query()->doesntExist() || QueuedClass::query()->doesntExist()) {
            $this->warn("Please run `php artisan app:create-project` first to create your first project and entry class");

            return self::FAILURE;
        }

        $project = $this->getProject();
        if (!$project) {
            $this->listProjects();

            return self::FAILURE;
        }

        $repeat = (int)($this->option('repeat') ?: 1);
        for ($i = 0; $i < $repeat; $i++) {
            $this->convertClasses($project->queuedClasses()->get());
        }

        return self::SUCCESS;
    }

    private function getProject(): ?Project
    {
        return Project::query()->find($this->option('project-id'));
    }

    private function listProjects(): void
    {
        $this->warn("Please provide a project-id");
        $projects = Project::query()->limit(51)->pluck('name', 'id');
        $projects->slice(0, 50)->each(function (string $projectName, $id) {
            $this->line("{$id}: {$projectName}");
        });

        if ($projects->count() === 51) {
            $count = Project::query()->count() - 50;
            $this->line("Not showing {$count} projects");
        }
    }

    private function convertClasses(Collection $queuedClasses): void
    {
        $this->line("Converting {$queuedClasses->count()} classes");

        $queuedClasses->each(function (QueuedClass $queuedClass) {
            echo '.';
            ClassConverter::fromQueuedClass($queuedClass);
        });

        $this->line('');
    }
}
