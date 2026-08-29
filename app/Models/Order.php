<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'order_code',
    'user_id',
    'shop_id',
    'address_id',
    'shipping_name',
    'shipping_phone',
    'shipping_line1',
    'shipping_city',
    'status',
    'payment_method',
    'payment_status',
    'subtotal',
    'shipping_fee',
    'discount',
    'total',
    'coupon_code',
    'notes',
    'tracking_code',
    'carrier',
    'placed_at',
    'paid_at',
    'packed_at',
    'shipped_at',
    'delivered_at',
])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'packed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_code ??= static::nextOrderCode();
            $order->placed_at ??= now();
        });
    }

    /**
     * Simple sequential code generator: ZC-1001, ZC-1002, ...
     * Fine for a single-DB setup; swap for a dedicated sequence/UUID
     * if you ever shard orders across databases.
     */
    public static function nextOrderCode(): string
    {
        $last = static::max('id') ?? 0;

        return 'ZC-' . (1000 + $last + 1);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(OrderTrackingEvent::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(OrderIssue::class);
    }
}
