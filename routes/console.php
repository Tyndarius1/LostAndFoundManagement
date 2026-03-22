<?php

use App\Models\FoundItem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $updatedCount = FoundItem::where('status', 'available')
        ->whereDate('date_found', '<=', Carbon::now()->subDays(30)->toDateString())
        ->update([
            'status' => 'archived',
            'updated_at' => now(),
        ]);

    Log::info('Scheduled archive-old-found-items executed.', [
        'archived_count' => $updatedCount,
    ]);
})->daily()->name('archive-old-found-items');
