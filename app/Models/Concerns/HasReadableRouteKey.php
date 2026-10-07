<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasReadableRouteKey
{
    public function getRouteKey(): string
    {
        $slug = Str::slug($this->name ?? '');

        return (string) $this->getKey().($slug !== '' ? '-'.$slug : '');
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field !== null && $field !== $this->getKeyName()) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        // The ID remains authoritative, so old links survive renaming and duplicate names.
        if (! preg_match('/\A([1-9][0-9]*)(?:-[a-z0-9]+(?:-[a-z0-9]+)*)?\z/', (string) $value, $matches)) {
            return $query->whereRaw('1 = 0');
        }

        // PostgreSQL bigint must not receive an out-of-range route parameter.
        $id = $matches[1];
        if (strlen($id) > 19 || (strlen($id) === 19 && strcmp($id, '9223372036854775807') > 0)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getQualifiedKeyName(), $id);
    }
}
