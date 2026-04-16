<?php

namespace App\Models;

use Database\Factories\ResourceBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceBlock extends Model
{
    /** @use HasFactory<ResourceBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'club_resource_id',
        'branch_id',
        'block_date',
        'start_time',
        'end_time',
        'reason',
        'blocked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'block_date' => 'date',
        ];
    }

    public function resource()
    {
        return $this->belongsTo(ClubResource::class, 'club_resource_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by_user_id');
    }
}
