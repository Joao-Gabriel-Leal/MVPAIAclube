<?php

namespace App\Models;

use Database\Factories\ResourceScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceSchedule extends Model
{
    /** @use HasFactory<ResourceScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'club_resource_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'slot_interval_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function resource()
    {
        return $this->belongsTo(ClubResource::class, 'club_resource_id');
    }
}
