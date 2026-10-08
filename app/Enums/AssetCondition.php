<?php

declare(strict_types=1);

namespace App\Enums;

enum AssetCondition: string
{
    case BAIK = 'baik';
    case RUSAK = 'rusak';
    case RUSAK_TOTAL = 'rusak_total';

    public function label(): string
    {
        return match ($this) {
            self::BAIK => 'Baik',
            self::RUSAK => 'Rusak',
            self::RUSAK_TOTAL => 'Rusak Total',
        };
    }
}
