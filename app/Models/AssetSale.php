<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetSale extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['sold_on' => 'date', 'amount' => 'decimal:2'];
}
