<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_PUBLIC = 'public';

    protected $fillable = [
        'context',
        'slot',
        'visibility',
        'original_name',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'checksum',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function binaryContent(): string
    {
        $content = $this->getAttribute('content');

        if (is_resource($content)) {
            rewind($content);

            return stream_get_contents($content) ?: '';
        }

        return (string) $content;
    }

    public function url(): string
    {
        return route('media.show', $this);
    }
}
