<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\NumeratorTrait;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Order Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Order",
 *
 *     @OA\Property(
 *         property="product_id",
 *         type="string",
 *         description="Product ID",
 *         example="9a778e5d-61aa-4a2b-b511-b445f6a67909"
 *     ),
 *     @OA\Property(
 *         property="investment_amount",
 *         type="number",
 *         description="Investment amount",
 *         example="100000"
 *     ),
 *     @OA\Property(
 *         property="deposit_percentage",
 *         type="number",
 *         description="Deposit percentage",
 *         example="10"
 *     ),
 *     @OA\Property(
 *         property="deposit_amount",
 *         type="number",
 *         description="Deposit amount",
 *         example="10000"
 *     ),
 * )
 */

/**
 * Class Order
 *
 * @package App\Models
 */
class Order extends Model
{
    use HasFactory;
    use NumeratorTrait;
    use OwnerTrait;
    use SoftDeletes;
    use UuidTrait;

    /**
     * Order status
     */
    const STATUS_NEW = 1;
    const STATUS_PARTLY_PAID = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_FAILED = 4;
    const STATUS_CANCELED = 5;

    /**
     * Order statuses array
     *
     * @var int[]
     */
    public static array $statuses = [
        self::STATUS_NEW,
        self::STATUS_PARTLY_PAID,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELED
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'product_id',
        'investment_amount',
        'deposit_percentage',
        'deposit_amount',
        'user_id',
        'status',
        'payload',
        'number',
        'amount_token',
        'amount_usd'
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
     * One Order have One Product relation
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // run on model boot

    /**
     * One Order have One Product relation
     *
     * @return HasMany
     */
    public function deposit(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * One Order have One Transaction relation
     *
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'order_id', 'id');
    }

    /**
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'ORD';
    }

    /**
     * Order create rules
     *
     * @return string[]
     */
    public static function validationRules(): array
    {
        return [
            'product_id' => 'required|string',
            'investment_amount' => 'required|integer|min:2500',
            'deposit_percentage' => 'required|integer|min:10|max:100',
            'deposit_amount' => 'required|integer|min:250',
        ];
    }
}
