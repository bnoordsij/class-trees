<?php

namespace Bnoordsij\ClassTrees\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $namespace
 * @property ?Project $project
 */
class NamespaceRule extends Model
{
    protected $table = 'class_namespace_rules';
    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
