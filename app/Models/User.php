<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'google_id',
    'phone',
    'preferred_payment',
    'alert_order',
    'alert_deal',
    'alert_sms',
    'email_verified_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'alert_order' => 'boolean',
            'alert_deal' => 'boolean',
            'alert_sms' => 'boolean',
        ];
    }

    /**
     * Every user gets the `customer` role automatically the moment
     * they're created — via a seeder, real registration, or an admin
     * creating an account directly. Fires once, on creation only, so
     * an admin can still freely remove it afterward via Step 8's
     * PUT /admin/users/{user}/roles without this silently re-adding it.
     *
     * Requires the `roles` table to already have a `customer` row
     * (RoleSeeder) — if it doesn't, this throws loudly rather than
     * failing silently, which is the right failure mode: a user with
     * no roles at all is a bug, not something to paper over.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->assignRole('customer');
        });
    }

    /**
     * A user's storefront, when they hold the vendor role.
     */
    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function followedShops(): HasMany
    {
        return $this->hasMany(FollowedShop::class);
    }

    /**
     * A user can hold multiple roles (customer, vendor, support), set
     * by an admin — no more single `users.role` enum.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function hasRole(string $slug): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('slug', $slug);
        }

        return $this->roles()->where('slug', $slug)->exists();
    }

    /**
     * @param  string[]  $slugs
     */
    public function hasAnyRole(array $slugs): bool
    {
        if (empty($slugs)) {
            return false;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('slug')->intersect($slugs)->isNotEmpty();
        }

        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    /**
     * @param  int|null  $assignedByAdminId  id on the `admins` table, not `users`
     */
    public function assignRole(string $slug, ?int $assignedByAdminId = null): void
    {
        $role = Role::where('slug', $slug)->firstOrFail();

        $this->roles()->syncWithoutDetaching([
            $role->id => ['assigned_by' => $assignedByAdminId],
        ]);
    }

    public function removeRole(string $slug): void
    {
        $role = Role::where('slug', $slug)->first();

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }
}
