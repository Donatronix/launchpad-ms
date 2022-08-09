<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\NumeratorTrait;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Purchase Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Purchase",
 *
 *     @OA\Property(
 *         property="product_id",
 *         type="string",
 *         description="Product ID",
 *         example="9a778e5d-61aa-4a2b-b511-b445f6a67909"
 *     ),
 *     @OA\Property(
 *         property="payment_amount",
 *         type="integer",
 *         description="Amount to pay",
 *         example="5000"
 *     ),
 *     @OA\Property(
 *         property="currency_ticker",
 *         type="string",
 *         description="currency to pay with",
 *         example="btc/usd/eur"
 *     )
 * )
 */
class Purchase extends Model
{
    use HasFactory;
    use NumeratorTrait;
    use SoftDeletes;
    use UuidTrait;

    /**
     * Deposit status
     */
    const STATUS_CREATED = 10;
    const STATUS_PROCESSING = 20;
    const STATUS_PARTIALLY_FUNDED = 30;
    const STATUS_SUCCEEDED = 40;
    const STATUS_FAILED = 50;
    const STATUS_CONFIRMED = 60;
    const STATUS_DELAYED = 70;
    const STATUS_CANCELED = 80;

    /**
     * Deposit statuses array
     *
     * @var int[]
     */
    public static array $statuses = [
        'created' => self::STATUS_CREATED,
        'processing' => self::STATUS_PROCESSING,
        'partially_funded' => self::STATUS_PARTIALLY_FUNDED,
        'confirmed' => self::STATUS_CONFIRMED,
        'delayed' => self::STATUS_DELAYED,
        'failed' => self::STATUS_FAILED,
        'succeeded' => self::STATUS_SUCCEEDED,
        'canceled' => self::STATUS_CANCELED
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'product_id',
        'payment_amount',
        'token_amount',
        'bonus',
        'total_token',
        'currency_ticker',
        'currency_type',
        'user_id',
        'status',
        'payment_order_id'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'PRS';
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
