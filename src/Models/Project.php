<?php

namespace Bnoordsij\ClassTrees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property string path
 * @property Collection $classes
 * @property Collection $queuedClasses
 * @property Collection $namespaceRules
 */
class Project extends Model
{
    use HasFactory;

    protected $table = 'class_projects';
    protected $guarded = [];

    public function classes(): HasMany
    {
        return $this->hasMany(Classe::class);
    }

    public function queuedClasses(): HasMany
    {
        return $this->hasMany(QueuedClass::class);
    }

    public function namespaceRules(): HasMany
    {
        return $this->hasMany(NamespaceRule::class);
    }
}
