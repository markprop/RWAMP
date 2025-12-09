<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Adds a ULID-based public identifier to a model.
 *
 * - Expects a nullable `ulid` CHAR(26) column.
 * - Automatically populates `ulid` on create when missing.
 * - Uses `ulid` for route model binding, so URLs expose ULIDs
 *   instead of auto‑increment integer primary keys.
 */
trait HasUlid
{
    /**
     * Boot the HasUlid trait for a model.
     */
    protected static function bootHasUlid(): void
    {
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Use ULID for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}


