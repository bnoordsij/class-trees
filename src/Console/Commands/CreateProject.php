<?php

namespace Bnoordsij\ClassTrees\Console\Commands;

use Bnoordsij\ClassTrees\Models\Project;
use Bnoordsij\ClassTrees\Services\ClassConverter;
use Bnoordsij\ClassTrees\Services\FqnToFile;
use Illuminate\Console\Command;

final class CreateProject extends Command
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
            $this->error($error);

            return self::FAILURE;
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

    private function validate(?string $name, string $path, ?string $entryClass): ?string
    {
        if (! $name) {
            return "Please provide a name";
        }

        $existingProject = Project::query()
            ->whereHas('classes')
            ->where('name', $name)->exists();
        if ($existingProject) {
            return "A project with this name already exists";
        }

        if (!file_exists($path) || !is_dir($path)) {
            return "Please provide an existing dir";
        }

        if (!is_readable($path) || !is_executable($path)) {
            return "Please provide a directory with read and execute permissions";
        }

        if (! $entryClass) {
            return "Please provide a starting class";
        }

        $file = FqnToFile::convert($path, $entryClass);
        if (!file_exists($file)) {
            return "Please provide an existing class";
        }

        if (!is_readable($file)) {
            return "Please provide a class with read permissions";
        }

        return null;
    }
}
