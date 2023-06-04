<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ClassTreeBuilder;

class ProjectController extends Controller
{
    public function tree(Project $project)
    {
        $project->classesCount = $project->classes->count();
        $project->queuedClassesCount = $project->queuedClasses->count();

        $tree = ClassTreeBuilder::fromProject($project);

        return view('projects.tree', compact('project', 'tree'));
    }
}
