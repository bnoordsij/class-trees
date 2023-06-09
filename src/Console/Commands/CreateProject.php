<?php

namespace Bnoordsij\ClassTrees\Console\Commands;

use Bnoordsij\ClassTrees\Models\Project;
use Bnoordsij\ClassTrees\Services\ClassConverter;
use Bnoordsij\ClassTrees\Services\FqnToFile;
use Illuminate\Console\Command;

class CreateProject extends Command
{
    protected $signature = 'class-trees:create-project
                            {--name= : Provide a name}
                            {--dir= : Provide a folder [default: pwd]}
                            {--class= : Provide the first class}';

    protected $description = 'Create a project';

    public function handle(): int
    {
        $name = $this->option('name');
        $path = $this->option('dir') ?: getcwd();
        $entryClass = $this->option('class');
        $error = $this->validate($name, $path, $entryClass);
        if ($error !== null) {
            return $error;
        }

        $project = Project::create([
            'name' => $name,
            'path' => $path,
        ]);

        $queuedClass = $project->queuedClasses()->create([
            'fqn' => $entryClass,
        ]);
        ClassConverter::fromQueuedClass($queuedClass);

        $this->info('Project created');

        return self::SUCCESS;
    }

    private function validate(?string $name, string $path, ?string $entryClass): ?int
    {
        if (! $name) {
            $this->warn("Please provide a name");

            return self::FAILURE;
        }

        $existingProject = Project::query()
            ->whereHas('classes')
            ->where('name', $name)->exists();
        if ($existingProject) {
            $this->warn("A project with this name already exists");

            return self::FAILURE;
        }

        if (!file_exists($path) || !is_dir($path)) {
            $this->warn("Please provide an existing dir");

            return self::FAILURE;
        }

        if (!is_readable($path) || !is_executable($path)) {
            $this->warn("Please provide a directory with read and execute permissions");

            return self::FAILURE;
        }

        if (! $entryClass) {
            $this->warn("Please provide a starting class");

            return self::FAILURE;
        }

        $file = FqnToFile::convert($path, $entryClass);
        if (!file_exists($file)) {
            $this->warn("Please provide an existing class");

            return self::FAILURE;
        }

        if (!is_readable($file)) {
            $this->warn("Please provide a class with read permissions");

            return self::FAILURE;
        }

        return null;
    }
}
