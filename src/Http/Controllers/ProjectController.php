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

        $tree = ClassTreeBuilder::fromProject($project);

        return view('projects.tree', compact('project', 'tree'));
    }
}
