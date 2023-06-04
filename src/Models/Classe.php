<?php

namespace App\Models;

use App\Enum\ClassRelation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property string $name
 * @property string $package
 * @property string $fqn
 * @property string $type
 * @property boolean $is_final
 * @property boolean $is_abstract
 * @property ?Project $project
 * @property ?Classe $extendsClass
 * @property Collection $imports
 * @property Collection $importedByClasses
 * @property Collection $extendedByClasses
 * @property Collection $queuedClasses
 */
class Classe extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function extendsClass(): BelongsTo
    {
        return $this->belongsTo(self::class, 'extends_id');
    }

    public function extendedByClasses(): HasMany
    {
        return $this->hasMany(self::class, 'extends_id');
    }

    public function queuedClasses(): HasMany
    {
        return $this->hasMany(QueuedClass::class, 'imported_by');
    }

    public function imports(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'class_imports', 'class_id', 'import_id');
    }

    public function importedByClasses(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'class_imports', 'import_id', 'class_id');
    }

    public function implements(): BelongsToMany
    {
        return $this->imports()->where('relation', ClassRelation::IMPLEMENT);
    }

    public function implementedByClasses(): BelongsToMany
    {
        return $this->implementedByClasses()->where('relation', ClassRelation::IMPLEMENT);
    }

    public function getGroup(): int
    {
        $fqn = strtolower($this->fqn);
        if (Str::startsWith($fqn, 'app')) {
            return 1;
        } elseif (Str::startsWith($fqn, 'modules')) {
            return 2;
        } elseif (Str::startsWith($fqn, 'maximum')) {
            return 3;
        } elseif (Str::startsWith($fqn, 'illuminate')) {
            return 4;
        } else {
            return 0;
        }
    }
}
