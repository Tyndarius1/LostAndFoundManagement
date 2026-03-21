<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClaimRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'claimant_id',
        'found_item_id',
        'proof_details',
        'proof_image_path',
        'status',
        'approved_by',
        'approved_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function foundItem(): BelongsTo
    {
        return $this->belongsTo(FoundItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}