<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * @property string $fqn
 * @property bool $file_exists
 * @property ?Project $project
 * @property ?Classe $extendedBy
 * @property ?Classe $implementedBy
 */
class QueuedClass extends Model
{
    protected $guarded = [];

    protected $casts = [
        'file_exists' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function imports(): BelongsToMany
    {
        return $this->belongsToMany(self::class);
    }

    public function extendedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'extended_by');
    }

    public function implementedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'implemented_by');
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

    public function getNameAttribute(): string
    {
        return Str::afterLast($this->fqn, '\\');
    }

    public function fileNotFound(): bool
    {
        return $this->file_exists === false;
    }
}
