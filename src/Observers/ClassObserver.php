<?php

namespace Bnoordsij\ClassTrees\Observers;

use Bnoordsij\ClassTrees\Models\Classe;
use Bnoordsij\ClassTrees\Models\QueuedClass;

class ClassObserver
{
    public function deleting(Classe $class)
    {
//        $this->addBackQueuedClasses($class);
        $this->cascadeUnusedImports($class);
    }

    private function addBackQueuedClasses(Classe $class): void
    {
        $class->importedByClasses
            ->each(function (Classe $importedByClass) use ($class) {
                return QueuedClass::create([
                    'project_id' => $class->project->id,
                    'fqn' => $class->fqn,
                    'imported_by' => $importedByClass->id, // unique needs to be added on this column as well
//                    'relation' => 'extended', // this needs to be changed first
                ]);
            });
    }

    private function cascadeUnusedImports(Classe $class): void
    {
        $class->imports
            ->reject(fn (Classe $class) => $class->importedByClasses->count() > 1) // imported by other class
            ->each
            ->delete();
    }
}
