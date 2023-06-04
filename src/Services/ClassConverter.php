<?php

namespace App\Services;

use App\Factory\ClassFactory;
use App\Models\Classe;
use App\Models\QueuedClass;

class ClassConverter
{
    public static function fromQueuedClass(QueuedClass $queuedClass)
    {
        return (new self())->convert($queuedClass);
    }

    public function convert(QueuedClass $queuedClass): ?Classe
    {
        $file = FqnToFile::convert($queuedClass->project->path, $queuedClass->fqn);
        $class = ClassFactory::fromFile($file, $queuedClass->project_id);

        if ($class) {
            $this->syncImport($queuedClass, $class);
            $queuedClass->delete();
        } else {
            $queuedClass->update(['file_exists' => false]);
        }

        return $class;
    }

    private function syncImport(QueuedClass $queuedClass, ?Classe $newClass): void
    {
        if (!$newClass || !$queuedClass->imported_by) {
            return;
        }

        if ($newClass->importedByClasses()->where('class_id', $queuedClass->imported_by)->exists()) {
            return;
        }

        if ($queuedClass->implemented_by === $newClass->id) {
            $newClass->implementedByClasses()->attach($queuedClass->imported_by);
        } elseif ($queuedClass->extended_by === $newClass->id) {
            $newClass->extendsClass()->associate($queuedClass->imported_by);
        }

        $newClass->importedByClasses()->attach($queuedClass->imported_by);
        $newClass->save();
    }
}
