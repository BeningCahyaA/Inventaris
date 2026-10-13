<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'depreciated_on' => 'date',
        'original_value' => 'decimal:2',
        'rate' => 'decimal:2',
        'opening_value' => 'decimal:2',
        'amount' => 'decimal:2',
        'closing_value' => 'decimal:2',
    ];
}
