<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat tugas yang sudah mendekati deadline';

    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminders): int
    {
        $count = $reminders->dispatchDue();

        $this->info("{$count} pengingat tugas dikirim.");

        return self::SUCCESS;
    }
}
