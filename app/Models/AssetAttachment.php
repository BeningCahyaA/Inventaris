<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type ?? '', 'image/')
            || in_array(strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
