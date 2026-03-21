<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function lostItems(): HasMany
    {
        return $this->hasMany(LostItem::class);
    }

    public function foundItems(): HasMany
    {
        return $this->hasMany(FoundItem::class);
    }
}