<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ResourceTypeCatalog
{
    public static function key(?string $type): string
    {
        $normalized = static::normalized($type);

        return $normalized === 'outros'
            ? 'outros'
            : (string) Str::slug($normalized, '-');
    }

    public static function label(?string $type): string
    {
        $normalized = static::normalized($type);

        if ($normalized === 'outros') {
            return 'Outros';
        }

        return collect(explode(' ', $normalized))
            ->filter()
            ->map(fn (string $segment) => Str::ucfirst($segment))
            ->implode(' ');
    }

    public static function matches(?string $selectedType, ?string $resourceType): bool
    {
        if (blank($selectedType)) {
            return true;
        }

        return static::key($resourceType) === $selectedType;
    }

    public static function options(iterable $resources): Collection
    {
        return collect($resources)
            ->map(fn ($resource) => [
                'value' => static::key(data_get($resource, 'type')),
                'label' => static::label(data_get($resource, 'type')),
            ])
            ->unique('value')
            ->sortBy(fn (array $option) => $option['label'] === 'Outros' ? 'zzz' : $option['label'])
            ->values();
    }

    public static function sections(iterable $resources): Collection
    {
        return collect($resources)
            ->groupBy(fn ($resource) => static::key(data_get($resource, 'type')))
            ->map(function (Collection $group, string $key) {
                return [
                    'key' => $key,
                    'label' => static::label(data_get($group->first(), 'type')),
                    'resources' => $group->values(),
                ];
            })
            ->sortBy(fn (array $section) => $section['label'] === 'Outros' ? 'zzz' : $section['label'])
            ->values();
    }

    public static function queryValue(string $selectedType): ?string
    {
        if ($selectedType === 'outros') {
            return null;
        }

        return str_replace('-', ' ', Str::lower($selectedType));
    }

    protected static function normalized(?string $type): string
    {
        $value = Str::of((string) $type)
            ->replaceMatches('/[_-]+/', ' ')
            ->squish()
            ->lower()
            ->value();

        return $value !== '' ? $value : 'outros';
    }
}
