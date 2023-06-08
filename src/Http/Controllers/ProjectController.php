<?php

namespace Bnoordsij\ClassTrees\Http\Controllers;

use Bnoordsij\ClassTrees\Models\Project;
use Bnoordsij\ClassTrees\Services\ClassTreeBuilder;

class ProjectController extends Controller
{
    public function tree(Project $project)
    {
        $project->classesCount = $project->classes->count();
        $project->queuedClassesCount = $project->queuedClasses->count();

        $projects = Project::query()
            ->withCount('queuedClasses')
            ->withCount('classes')
            ->get();
        dump('$projects', $projects);

        dump(['$project', $project,
            $project->classes()->count(),
        ]);

        $tree = ClassTreeBuilder::fromProject($project);
        dd('$tree', $tree);

        return view('projects.tree', compact('project', 'tree'));
    }
}
