<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 *         property="product",
 *         type="string",
 *         description="Purchased token",
 *         example="$utta"
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
 *     )
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
        'contributor_id',
        'status',
        'payload'
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
     * Order create rules
     *
     * @return string[]
     */
    public static function validationRules(): array
    {
        return [
            'product' => 'required|string',
            'investment_amount' => 'required|integer|min:2500',
            'deposit_percentage' => 'required|integer|min:10|max:100',
            'deposit_amount' => 'required|integer|min:250'
        ];
    }

    /**
     * One Order have One Contributor relation
     *
     * @return BelongsTo
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(Contributor::class);
    }

    /**
     * One Order have One Product relation
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
