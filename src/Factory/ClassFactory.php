<?php

namespace Bnoordsij\ClassTrees\Factory;

use Bnoordsij\ClassTrees\Models\Classe;
use Bnoordsij\ClassTrees\Models\NamespaceRule;
use Bnoordsij\ClassTrees\Models\QueuedClass;
use Bnoordsij\ClassTrees\Services\FqnToFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassFactory
{
    private Collection $imports;
    private ?int $projectId = null;
    private ?Classe $class;

    public static function fromFile(string $file, ?int $projectId = null): ?Classe
    {
        return (new static())->make($file, $projectId);
    }

    public function make(string $file, ?int $projectId = null): ?Classe
    {
        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $lines = array_filter(explode("\n", Str::before($content, '{')));
        $lastLine = last($lines);

        $this->projectId = $projectId;
        $this->makeClass($file, $lastLine, $lines);

        $existingClass = Classe::query()
            ->where('project_id', $projectId)
            ->where('fqn', $this->class->fqn)
            ->first();

        if ($existingClass) {
            $this->class = $existingClass;
        } else {
            $this->class->save();
        }

        $this->queueImports($lastLine);

        return $this->class;
    }

    private function makeClass(string $file, string $lastLine, array $lines): void
    {
        $this->imports = new Collection();
        $this->class = new Classe();
        $this->class->project_id = $this->projectId;
        $this->class->name = Str::afterLast(Str::before($file, '.php'), '/');
        $this->class->is_final = Str::startsWith($lastLine, 'final ');
        $this->class->is_abstract = Str::startsWith($lastLine, 'abstract ');

        preg_match('/trait|class|interface/', $lastLine, $matches);
        $this->class->type = current($matches);

        foreach ($lines as $line) {
            $line = trim($line);
            if (Str::startsWith($line, 'namespace')) {
                $this->class->fqn = Str::between($line, 'namespace ', ';') . '\\' . $this->class->name;
            }
            if (Str::startsWith($line, 'use ')) {
                $this->imports[] = Str::between($line, 'use ', ';');
            }
        }

        $this->class->package = FqnToFile::findPackage($this->class->fqn);
    }

    private function queueImports(string $lastLine): void
    {
        $queuedFqns = QueuedClass::query()->where('project_id', $this->projectId)->orderBy('fqn')->pluck('fqn');
        $namespaceRules = NamespaceRule::query()->where('project_id', $this->projectId)->orderBy('namespace')->pluck('namespace');

        $implements = $this->queueImplementedClasses($lastLine);
        $extend = $this->queueExtendedClass($lastLine);

        $queuedClasses = $this->imports
            ->flatMap(fn (string $import) => [Str::after(Str::afterLast($import, '\\'), ' as ') => Str::before($import, ' as ')]) // (alias ?? fqn) => fqn
            ->reject(fn (string $fqn) => $queuedFqns->contains($fqn))
            ->filter(fn (string $fqn) => $this->filterNamespaceRules($namespaceRules, $fqn))
            ->map(function (string $fqn, string $alias) use ($implements, $extend) {
                return QueuedClass::firstOrCreate([
                    'project_id' => $this->projectId,
                    'fqn' => $fqn,
                    'implemented_by' => in_array($alias, $implements) ? $this->class->id : null,
                    'extended_by' => $alias === $extend ? $this->class->id : null,
                    'imported_by' => $this->class->id,
                ]);
            });

        $this->createExtendClass($extend, $queuedClasses);
    }

    private function queueExtendedClass(string $lastLine): ?string
    {
        if (! Str::contains($lastLine, 'extends ')) {
            return null;
        }

        return Str::after($lastLine, 'extends ');
    }

    private function queueImplementedClasses(string $lastLine): array
    {
        if (! Str::contains($lastLine, 'implements ')) {
            return [];
        }

        $implements = Str::after($lastLine, 'implements ');
        $implements = str_replace(' ', '', $implements);
        $implements = explode(',', $implements); // implements Authenticatable, Filterable

        return $implements;
    }

    private function filterNamespaceRules(Collection $namespaceRules, string $fqn): bool
    {
        if ($namespaceRules->isEmpty()) {
            return true;
        }

        foreach ($namespaceRules as $namespaceRule) {
            $namespaceRule = Str::start($namespaceRule, '\\');
            $fqn = Str::start($fqn, '\\');
            if ($fqn === $namespaceRule) {
                return true;
            }

            if (Str::endsWith($namespaceRule, '*')) {
                if (Str::startsWith($fqn, str_replace('*', '', $namespaceRule))) {
                    return true;
                }
            }
        }

        return false;
    }

    private function createExtendClass(?string $extend, Collection $queuedClasses): void
    {
        if (! $extend) {
            return;
        }

        $extendedClassExists = $queuedClasses->where('extended_by', $this->class->id)->isNotEmpty();
        if ($extendedClassExists) {
            return;
        }
        $extendFqn = $extend;
        if (! Str::contains($extend, '\\')) {
            // extends class in same namespace
            // App\Http\Controllers\UserController extends Controller
            $extendFqn = str_replace($this->class->name, $extend, $this->class->fqn);
        }

        QueuedClass::firstOrCreate([
            'project_id' => $this->projectId,
            'fqn' => $extendFqn,
            'imported_by' => $this->class->id,
            'extended_by' => $this->class->id,
        ]);
    }
}
