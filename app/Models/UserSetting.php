<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'notifications_enabled',
    'default_reminder_minutes',
    'sound_enabled',
])]
class UserSetting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notifications_enabled' => 'boolean',
            'sound_enabled' => 'boolean',
            'default_reminder_minutes' => 'integer',
        ];
    }

    /** @return BelongsTo<User, UserSetting> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
