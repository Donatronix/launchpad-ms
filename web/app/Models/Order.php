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
 *      @OA\Property(
 *         property="payment_type_id",
 *         type="number",
 *         description="Payment Type ID, 1 - Fiat, 2 - Crypto",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="wallet_address",
 *         type="number",
 *         description="Wallet address of the transaction",
 *         example="576894-erjt-4059"
 *     ),
 *     @OA\Property(
 *         property="credit_card_type_id",
 *         type="number",
 *         description="Credit Card Type, Visa - 1, Master Card - 2",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="currency_id",
 *         type="string",
 *         description="Deposit currency id",
 *         example="967a6aac-b6dc-4aa7-a6cd-6a612e39d4ee"
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

    // prefixes
    const ORD = "orders";
    const DEP = "deposits";
    const PRS = "purchases";

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
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'OR';
    }

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

    // run on model boot

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
            'payment_type_id' => 'required|integer|exists:payment_types,id',
            'wallet_address' => 'required|string',
            'currency_id' => 'required|string',
        ];
    }

    /**
     * One Order have One Product relation
     *
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * One Order have One Product relation
     *
     * @return BelongsTo
     */
    public function deposit()
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * One Order have One Transaction relation
     *
     * @return BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'id', 'order_id');
    }

}
