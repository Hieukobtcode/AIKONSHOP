<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Transaction;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'telegram',
        'zalo',
        'bio',
        'birthday',
        'role',
        'is_active',
        'is_banned',
        'banned_until',
        'referral_code',
        'referred_by',
        'affiliate_earnings',
        'points',
        'balance',
        'total_deposited',
        'kyc_verified',
        'kyc_id_card',
        'kyc_selfie',
        'total_orders',
        'total_spent',
        'successful_orders',
        'last_order_at',
        'last_login_at',
        'last_login_ip',
        'social_providers',
        'google_id',
        'facebook_id',
        'notify_order',
        'notify_promotion',
        'notify_account',
        'login_attempts',
        'locked_until',
        'register_ip',
        'register_user_agent',
        'verification_token',
        'verification_sent_at',
        'recaptcha_score',
        'device_fingerprint'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday'          => 'date',
        'banned_until'      => 'datetime',
        'last_order_at'     => 'datetime',
        'last_login_at'     => 'datetime',
        'locked_until'      => 'datetime',
        'verification_sent_at' => 'datetime',
        'social_providers'  => 'array',

        // Boolean
        'is_active'         => 'boolean',
        'is_banned'         => 'boolean',
        'kyc_verified'      => 'boolean',
        'notify_order'      => 'boolean',
        'notify_promotion'  => 'boolean',
        'notify_account'    => 'boolean',

        // Số tiền
        'balance'           => 'integer',
        'total_deposited'   => 'integer',
        'affiliate_earnings'=> 'integer',
        'points'            => 'integer',
        'total_spent'       => 'integer',

        'login_attempts'    => 'integer',
        'recaptcha_score'   => 'float',
    ];

    // ===================================================================
    // QUAN HỆ
    // ===================================================================
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // public function referrals()
    // {
    //     return $this->hasMany(UserReferral::class, 'user_id');
    // }

    // public function referredBy()
    // {
    //     return $this->belongsTo(User::class, 'referred_by');
    // }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class);
    // }

    // public function reviews()
    // {
    //     return $this->hasMany(Review::class);
    // }

    // ===================================================================
    // ACCESSOR
    // ===================================================================
    public function getDisplayNameAttribute()
    {
        return $this->name . ($this->kyc_verified ? ' (Đã xác minh)' : '');
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('images/default-avatar.png');
    }

    public function getBalanceFormattedAttribute()
    {
        return number_format($this->balance) . 'đ';
    }

    public function getTotalDepositedFormattedAttribute()
    {
        return number_format($this->total_deposited) . 'đ';
    }

    public function getTotalSpentFormattedAttribute()
    {
        return number_format($this->total_spent) . 'đ';
    }

    // ===================================================================
    // SCOPE TIỆN DỤNG
    // ===================================================================
    public function scopeCustomer($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_banned', false);
    }

    public function scopeHasBalance($query, $min = 10000)
    {
        return $query->where('balance', '>=', $min);
    }

    // ===================================================================
    // HELPER METHODS
    // ===================================================================
    public function deposit(int $amount, string $note = null)
    {
        if ($amount <= 0) {
            throw new \Exception('Số tiền phải lớn hơn 0');
        }

        $this->increment('balance', $amount);
        $this->increment('total_deposited', $amount);

        // Ghi log giao dịch
        Transaction::create([
            'user_id' => $this->id,
            'type'    => 'deposit',
            'amount'  => $amount,
            'note'    => $note,
        ]);
    }

    public function withdraw(int $amount, string $note = null)
    {
        if ($amount <= 0) {
            throw new \Exception('Số tiền phải lớn hơn 0');
        }

        if ($this->balance < $amount) {
            throw new \Exception('Số dư không đủ');
        }

        $this->decrement('balance', $amount);
        $this->increment('total_spent', $amount);

        // Ghi log giao dịch
        Transaction::create([
            'user_id' => $this->id,
            'type'    => 'withdraw',
            'amount'  => $amount,
            'note'    => $note,
        ]);
    }
}
