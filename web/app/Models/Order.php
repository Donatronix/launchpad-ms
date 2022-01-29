<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Contributor Person Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Order",
 *
 *     @OA\Property(
 *         property="purchased_token_id",
 *         type="string",
 *         description="Purchased token",
 *         example="utta"
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

    /**
     * Order status
     */
    const STATUS_NEW = 1;
    const STATUS_INSUFFICIENT_FUNDS = 2;
    const STATUS_PAID = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_FAILED = 5;
    const STATUS_CANCELED = 6;


//s$SLAPA - Synthetic SLAPA Token
//$SLAPA - SLAPA Token
//$DIVIT - DIVIT Token
//$UTTA - UTTA Token

    /**
     * Order statuses array
     *
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_NEW,
        self::STATUS_INSUFFICIENT_FUNDS,
        self::STATUS_PAID,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELED
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'purchased_token_id',
        'investment_amount',
        'deposit_percentage',
        'deposit_amount',
        'contributor_id',
        'status',
        'payload'
    ];

    /**
     * One Order has one Contributor relation
     *
     * @return BelongsTo
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(Contributor::class);
    }
}
