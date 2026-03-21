<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LostItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'item_name',
        'description',
        'color',
        'last_seen_location',
        'date_lost',
        'image_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_lost' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}