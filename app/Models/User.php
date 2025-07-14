<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Company;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\SubscriptionUser;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /** 
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }
    /**
     * The roles that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id');
    }
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }
    /**
     * Get the company that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Get all subscription users for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptionUsers(): HasMany
    {
        return $this->hasMany(SubscriptionUser::class);
    }

    /**
     * Get the current active subscription for this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(SubscriptionUser::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->latest('created_at');
    }

    /**
     * Check if user has active subscription
     *
     * @return bool
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptionUsers()->where('status', 'active')->exists();
    }

    /**
     * Get user's current subscription plan name
     *
     * @return string
     */
    public function getCurrentPlan(): string
    {
        $subscription = $this->currentSubscription()->with('subscription')->first();
        return $subscription ? $subscription->subscription->plan : 'Free';
    }
}
