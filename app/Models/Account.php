<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Class Account
 *
 * Represents a bank account in the system.
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $sheba_number
 * @property float $balance
 * @property float $reserved
 * @property float $available_balance
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'sheba_number',
        'balance',
        'reserved',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'reserved' => 'decimal:2',
        'uuid' => 'string',
    ];

    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'balance' => 0.00,
        'reserved' => 0.00,
    ];

    /**
     * Boot the model and set the UUID automatically.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the available balance (balance - reserved).
     *
     * @return float
     */
    public function getAvailableBalanceAttribute(): float
    {
        return $this->balance - $this->reserved;
    }

    /**
     * Get the user that owns the account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the paya requests originating from this account.
     */
    public function payaRequests(): HasMany
    {
        return $this->hasMany(PayaRequest::class, 'from_account_id');
    }

    /**
     * Get the transactions for this account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
