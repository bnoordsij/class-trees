<?php

namespace Bnoordsij\ClassTrees\Http\Controllers;

use Bnoordsij\ClassTrees\Models\Project;
use Bnoordsij\ClassTrees\Models\QueuedClass;
use Bnoordsij\ClassTrees\Services\ClassTreeBuilder;

class ProjectController extends Controller
{
    public function tree($project)
    {
        $project = Project::query()->findOrFail($project);

        $project->classesCount = $project->classes->count();
        $project->queuedClassesCount = $project->queuedClasses->count();

        $tree = ClassTreeBuilder::fromProject($project);

        return view('class-trees::projects.tree', compact('project', 'tree'));
    }
}
