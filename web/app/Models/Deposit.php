<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\NumeratorTrait;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Deposit User Access Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="DepositUserAccess",
 *
 *     @OA\Property(
 *         property="amount",
 *         type="integer",
 *         format="int32",
 *         description="Deposit amount",
 *         example="10000"
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency of deposit (Currency code (USD) or Currency ID (0006faf6-7a61-426c-9034-579f2cfcfa83))",
 *
 *         @OA\Examples(example="string", value="USD", summary="Currency code"),
 *         @OA\Examples(example="uuid", value="0006faf6-7a61-426c-9034-579f2cfcfa83", summary="Currency ID"),
 *     ),
 * )
 */

/**
 * Deposit Admin Access Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="DepositAdminAccess",
 *
 *     @OA\Property(
 *         property="amount",
 *         type="integer",
 *         format="int32",
 *         description="Deposit amount",
 *         example="10000"
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency of deposit (Currency code (USD) or Currency ID (0006faf6-7a61-426c-9034-579f2cfcfa83))",
 *
 *         @OA\Examples(example="string", value="USD", summary="Currency code"),
 *         @OA\Examples(example="uuid", value="0006faf6-7a61-426c-9034-579f2cfcfa83", summary="Currency ID"),
 *     ),
 *     @OA\Property(
 *         property="related_id",
 *         type="string",
 *         description="Based on object id",
 *         example="967a6aac-aaaa-aaaa-0000-6a612e39d4ee"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         description="User id",
 *         example="967a6aac-b6dc-4aa7-a6cd-6a612e39d4ee"
 *     )
 * )
 */
class Deposit extends Model
{
    use HasFactory;
    use NumeratorTrait;
    use OwnerTrait;
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
        'amount',
        'currency_code',
        'order_id',
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
        return 'DEP';
    }

    /**
     * One Deposit have One Product relation
     *
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Deposit create rules
     *
     * @return string[]
     */
    public static function validationRules(): array
    {
        return [
            'amount' => 'required|integer|min:250',
            'currency' => 'required|string|min:3',
        ];
    }
}
