<?php

namespace App\Models;

use App\Enums\PayaRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Class PayaRequest
 *
 * Represents a Paya transfer request in the system.
 *
 * @property string $uuid
 * @property int $from_account_id
 * @property string $to_sheba_number
 * @property float $price
 * @property PayaRequestStatus $status
 * @property string|null $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PayaRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'from_account_id',
        'to_sheba_number',
        'price',
        'status',
        'note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'status' => PayaRequestStatus::class,
        'created_at' => 'datetime:Y-m-d\TH:i:sP', // فرمت ISO 8601 برای JSON
    ];

    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'status' => PayaRequestStatus::PENDING,
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
     * Get the account that the request originates from.
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Get the transaction associated with this request.
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'reference_id', 'id');
    }

    public const CHANGEABLE_STATUSES = [
        PayaRequestStatus::PENDING,
    ];

    public const TARGET_STATUSES = [
        PayaRequestStatus::CONFIRMED,
        PayaRequestStatus::CANCELED,
    ];
}
