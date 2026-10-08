<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assets';

    protected $fillable = [
        'asset_tag',
        'name',
        'person_in_charge',
        'purchase_date',
        'warranty_expiry_date',
        'category',
        'status',
        'condition',
        'price',
        'description',
        'location',
        'vendor',
        'serial_number',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(AssetLog::class)->latest();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AssetTransaction::class)->latest('borrowed_at');
    }

    public function activeTransaction(): HasOne
    {
        return $this->hasOne(AssetTransaction::class)
            ->whereNull('returned_at')
            ->latestOfMany('borrowed_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssetAttachment::class)->latest();
    }
}
