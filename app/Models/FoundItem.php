<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoundItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'category_id',
        'item_name',
        'description',
        'color',
        'found_location',
        'date_found',
        'image_path',
        'qr_code_path',
        'reference_code',
        'storage_location',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_found' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function claimRequests(): HasMany
    {
        return $this->hasMany(ClaimRequest::class);
    }
    
}