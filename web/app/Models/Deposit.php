<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sumra\SDK\Traits\OwnerTrait;
use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Deposit Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Deposit",
 *
 *     @OA\Property(
 *         property="currency_id",
 *         type="string",
 *         description="Currency Id",
 *         example="967a6aac-b6dc-4aa7-a6cd-6a612e39d4ee"
 *     ),
 *     @OA\Property(
 *         property="deposit_amount",
 *         type="number",
 *         description="deposit amount",
 *         example="100000"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         description="User id",
 *         example="967a6aac-b6dc-4aa7-a6cd-6a612e39d4ee"
 *     ),
 * )
 */
class Deposit extends Model
{
    use HasFactory;
    use UuidTrait;
    use OwnerTrait;
    use SoftDeletes;

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
            'currency_id' => 'required|string',
        ];
    }

    /**
     * One Deposit have One Product relation
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}