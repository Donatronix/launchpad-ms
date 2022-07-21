<?php

namespace App\Models;

use App\Traits\NumeratorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Deposit Scheme
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
 *         property="currency_id",
 *         type="string",
 *         description="Currency Id",
 *         example="967a6aac-b6dc-4aa7-a6cd-6a612e39d4ee"
 *     ),
 *     @OA\Property(
 *         property="currency_code",
 *         type="string",
 *         description="Currency code",
 *         example="USD"
 *     ),
 *     @OA\Property(
 *         property="object_id",
 *         type="string",
 *         description="Based on object id",
 *         example="967a6aac-aaaa-aaaa-0000-6a612e39d4ee"
 *     )
 * )
 */
/**
 * Deposit Scheme
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
 *         property="currency_id",
 *         type="string",
 *         description="Currency Id",
 *         example="967a6aac-b6dc-4aa7-a6cd-6a612e39d4ee"
 *     ),
 *     @OA\Property(
 *         property="currency_code",
 *         type="string",
 *         description="Currency code",
 *         example="USD"
 *     ),
 *     @OA\Property(
 *         property="object_id",
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
    const STATUS_CREATED = 1;
    const STATUS_PAID = 2;
    const STATUS_FAILED = 3;
    const STATUS_CANCELED = 4;

    /**
     * Order statuses array
     *
     * @var int[]
     */
    public static array $statuses = [
        self::STATUS_CREATED,
        self::STATUS_PAID,
        self::STATUS_FAILED,
        self::STATUS_CANCELED
    ];

    /**
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'DE';
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'amount',
        'currency_code',
        'order_id',
        'status',
        'user_id',
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

    /**
     * One Deposit have One Product relation
     *
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
