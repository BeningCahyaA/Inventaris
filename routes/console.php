<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan pengecekan overdue setiap hari jam 07:00 WIB
Schedule::command('assets:mark-overdue')->dailyAt('07:00');
Schedule::command('assets:depreciate')->dailyAt('00:10')->timezone('Asia/Jakarta')->withoutOverlapping();
