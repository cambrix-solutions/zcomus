<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App-specific inbox notification (spec §19.13 / §20.16). Not related
 * to Illuminate\Notifications — this is a plain Eloquent model over a
 * plain `notifications` table. See the migration comment for the
 * naming collision heads-up if you ever add Laravel's own
 * database notification channel too.
 */
#[Fillable(['user_id', 'title', 'body', 'tone', 'icon', 'read_at'])]
class Notification extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
