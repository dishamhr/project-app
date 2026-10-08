<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Penjadwalan Pengingat Tugas
|--------------------------------------------------------------------------
|
| Pengingat tugas dicek setiap menit sehingga notifikasi muncul tepat
| pada waktu pengingat yang dipilih mahasiswa.
|
*/

Schedule::command('reminders:send')->everyMinute();
