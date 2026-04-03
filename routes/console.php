<?php

use App\Services\ArchiveService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:archive-closed-month {month?}', function (?string $month = null) {
    $result = app(ArchiveService::class)->archiveClosedMonth($month);

    if (($result['status'] ?? 'failed') === 'success') {
        $this->info('Archive completed successfully.');
    } else {
        $this->error('Archive failed: '.($result['error'] ?? 'Unknown error'));
        return;
    }

    $this->line('Month: '.($result['month'] ?? 'N/A'));
    $this->line('From: '.($result['from'] ?? 'N/A'));
    $this->line('To: '.($result['to'] ?? 'N/A'));

    $tables = $result['tables_archived'] ?? [];
    foreach ($tables as $table => $count) {
        $this->line($table.': '.$count.' archived rows');
    }

    if (isset($result['sales_deleted'])) {
        $this->line('sales deleted: '.$result['sales_deleted']);
    }

    if (isset($result['purchases_deleted'])) {
        $this->line('purchases deleted: '.$result['purchases_deleted']);
    }
})->purpose('Archive a closed month of transactional data to archive tables');
