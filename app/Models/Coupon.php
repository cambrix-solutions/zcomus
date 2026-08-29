<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'title', 'type', 'value', 'min_order', 'rule', 'starts_at', 'ends_at', 'is_active'])]
class Coupon extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function isCurrentlyValid(): bool
    {
        return $this->is_active
            && now()->between($this->starts_at, $this->ends_at);
    }
}
