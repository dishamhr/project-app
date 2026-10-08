<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'subject',
    'description',
    'lecturer',
    'deadline_date',
    'deadline_time',
    'priority',
    'reminder_minutes',
    'status',
    'reminded_at',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reminded_at' => 'datetime',
        ];
    }

    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    /**
     * Status tugas dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'Belum Dikerjakan',
            self::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            self::STATUS_DONE => 'Selesai',
        ];
    }

    /**
     * Prioritas tugas dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public static function priorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Rendah',
            self::PRIORITY_MEDIUM => 'Sedang',
            self::PRIORITY_HIGH => 'Tinggi',
        ];
    }

    /**
     * Pilihan waktu pengingat (menit sebelum deadline).
     *
     * @return array<int, string>
     */
    public static function reminderOptions(): array
    {
        return [
            30 => '30 menit sebelum deadline',
            60 => '1 jam sebelum deadline',
            180 => '3 jam sebelum deadline',
            1440 => '1 hari sebelum deadline',
            2880 => '2 hari sebelum deadline',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function reminderMinutes(): array
    {
        return array_keys(self::reminderOptions());
    }

    /**
     * @return array<string, string>
     */
    public static function filterOptions(): array
    {
        return [
            'all' => 'Semua',
            self::STATUS_NOT_STARTED => 'Belum Dikerjakan',
            self::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            self::STATUS_DONE => 'Selesai',
            'high' => 'Prioritas Tinggi',
        ];
    }

    /** @return BelongsTo<User, Task> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Reminder, Task> */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Gabungan tanggal dan jam deadline.
     */
    public function deadlineAt(): Carbon
    {
        return Carbon::parse("{$this->deadline_date} {$this->deadline_time}");
    }

    /**
     * Waktu tepat pengingat harus dikirim.
     */
    public function remindAt(): Carbon
    {
        return $this->deadlineAt()->copy()->subMinutes((int) $this->reminder_minutes);
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function priorityLabel(): string
    {
        return self::priorities()[$this->priority] ?? $this->priority;
    }

    public function reminderLabel(): string
    {
        return self::reminderOptions()[(int) $this->reminder_minutes] ?? "{$this->reminder_minutes} menit sebelum deadline";
    }

    /**
     * Tugas yang belum selesai dan belum dikirim pengingatnya.
     */
    public function scopePendingReminder(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', self::STATUS_DONE)
            ->whereNull('reminded_at')
            ->whereDate('deadline_date', '>=', now()->toDateString());
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Urutkan berdasarkan deadline terdekat.
     */
    public function scopeOrderedByDeadline(Builder $query): Builder
    {
        return $query->orderBy('deadline_date')->orderBy('deadline_time');
    }
}
