<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
        'status',
        'approved_by',
        'approved_at',
        'rejection_note',
    ];

    // Otomatis mengubah kolom JSON menjadi Array di PHP
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'approved_at' => 'datetime',
    ];

    // Relasi ke tabel Assets
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}