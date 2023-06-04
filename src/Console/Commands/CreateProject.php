<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class CreateProject extends Command
{
    protected $signature = 'app:create-project
                            {--name= : Provide a name}
                            {--dir= : Provide a folder [default: pwd]}
                            {--class= : Provide the first class}';

    protected $description = 'Create a project';

    public function handle(): int
    {
        $name = $this->option('name');
        $path = $this->option('dir') ?: getcwd();
        $entryClass = $this->option('class');
        $error = $this->validate($name, $entryClass);
        if ($error !== null) {
            return $error;
        }

        $project = Project::create([
            'name' => $name,
            'path' => $path,
        ]);

        $project->queuedClasses()->create([
            'fqn' => $entryClass,
        ]);

        $this->info('Project created');

        return self::SUCCESS;
    }

    private function validate(?string $name, ?string $entryClass): ?int
    {
        if (! $name) {
            $this->warn("Please provide a name");

            return self::FAILURE;
        }

        $existingProject = Project::query()->where('name', $name)->exists();
        if ($existingProject) {
            $this->warn("A project with this name already exists");

            return self::FAILURE;
        }

        if (! $entryClass) {
            $this->warn("Please provide a starting class");

            return self::FAILURE;
        }

        // check if class exists based on path
        if (0) {
            $this->warn("Please provide an existing class");

            return self::FAILURE;
        }

        return null;
    }
}
