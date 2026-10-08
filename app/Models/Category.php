<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'prefix',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category', 'slug');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CategoryLog::class);
    }
}
