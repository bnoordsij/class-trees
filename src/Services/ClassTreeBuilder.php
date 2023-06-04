<?php

namespace Bnoordsij\ClassTrees\Services;

use Bnoordsij\ClassTrees\Models\Classe;
use Bnoordsij\ClassTrees\Models\Project;
use Illuminate\Support\Collection;

class ClassTreeBuilder
{
    private Collection $classes;
    private array $depths = [];

    public static function fromProject(Project $project): object
    {
        return (new self)->build($project);
    }

    public function build(Project $project): object
    {
        $this->classes = $this->getAllClasses($project);
        $this->determineDepth();

        return (object)[
            'nodes' => $this->getNodes(),
            'links' => $this->getLinks(),
        ];
    }

    private function getAllClasses(Project $project): Collection
    {
        return $project->classes()
            ->withCount('importedByClasses') // top down ordering
            ->with(['imports', 'importedByClasses'])
            ->orderBy('fqn')
            ->orderBy('imported_by_classes_count') // number of usages
            ->get();
    }

    private function getNodes(): Collection
    {
        return $this->classes->map(fn (Classe $class) => [
            'id' => $class->id,
            'title' => $class->name,
            'group' => $class->getGroup(),
            'style' => $this->getDepth($class->depth),
        ]);
    }

    private function getLinks(): Collection
    {
        return $this->classes
            ->pluck('imports')
            ->flatten()
            ->map(fn (Classe $import) => [
                'source' => $import->getOriginal('pivot_class_id'),
                'target' => $import->getOriginal('pivot_import_id'),
            ]);
    }

    private function determineDepth(): void
    {
        $links = $this->getLinks();

        $level0 = $this->classes->filter(fn (Classe $class) => $class->importedByClasses->isEmpty())->pluck('id')->toArray();

        // null => [40]
        // 40 => [41, 42]
        // 41 => [43, 44, 45, 46]
        // 42 => [47]
        // 47 => [48]
//            $this->levels[0] = [40];
//            $this->levels[1] = [41, 42];
//            $this->levels[2] = [43, 44, 45, 46, 47];
//            $this->levels[3] = [48];

        $levels[] = $idsGrouped = $level0;
        for ($i = 0; $i < 8; $i++) { // max 8 levels deep for now
            $prevIds = $levels[$i];
            $ids = $links->whereIn('source', $prevIds)->pluck('target')->toArray();
            $ids = array_diff($ids, $idsGrouped); // skip those we've already grouped
            if (empty($ids)) {
                break;
            }
            $levels[$i+1] = $ids;
            $idsGrouped = array_merge($idsGrouped, $ids);
        }

        // dump all remaining ids into last level
        $remaining = $links->whereNotIn('target', $idsGrouped)->pluck('target')->toArray();
        if (count($remaining)) {
            $levels[] = $remaining;
        }

        $this->buildDepths(count($levels));

        // invert array structure, easier to do it like this
        $depths = [];
        foreach ($levels as $level => $ids) {
            foreach ($ids as $id) {
                $depths[$id] = $level;
            }
        }

        $this->classes = $this->classes->map(function (Classe $class) use ($depths) {
            $class->depth = $depths[$class->id] ?? 0;

            return $class;
        });
    }

    private function getDepth(int $depth): string
    {
        $values = $this->depths[$depth] ?? $this->depths[0];

        if (!$values) {
            return '';
        }

        return 'font-size: ' . $values['fontSize'] . 'px; opacity: ' . $values['opacity'] . ';';
    }

    private function buildDepths(int $total)
    {
        if ($this->depths) {
            return;
        }

        $minOpacity = 0.4;
        $maxOpacity = 1;

        $minFontSize = 6;
        $maxFontSize = 12;

        if ($total === 0) {
            $this->depths[0] = [
                'opacity' => $maxOpacity,
                'fontSize' => $maxFontSize,
            ];
            return;
        }

        for ($i = 0; $i <= $total; $i++) {
            $this->depths[$i] = [
                'opacity' => $maxOpacity - round(($maxOpacity - $minOpacity) * ($i / $total), 2),
                'fontSize' => $maxFontSize - round(($maxFontSize - $minFontSize) * ($i / $total)),
            ];
        }
    }
}
