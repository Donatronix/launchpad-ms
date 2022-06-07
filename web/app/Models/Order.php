<?php

namespace App\Models;

use App\Traits\RandomCharGeneratorTrait;
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
    use UuidTrait;
    use RandomCharGeneratorTrait;

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
     * @var string[]
     */
    protected $fillable = [
        'product_id',
        'investment_amount',
        'deposit_percentage',
        'deposit_amount',
        'contributor_id',
        'status',
        'payload',
        'order_no',
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
            'product' => 'required|string',
            'investment_amount' => 'required|integer|min:2500',
            'deposit_percentage' => 'required|integer|min:10|max:100',
            'deposit_amount' => 'required|integer|min:250',
            'payment_type_id' => 'required|integer|exists:payment_types,id',
            'wallet_address' => 'required',
            'currency_id' => 'required|string',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        // generate the order numebr when creating a new order model
        self::creating(function ($model) {
            $order_no = $model->getRandomChar(12);

            // generate new order number while the generated one exists
            while (Order::where('order_no', $order_no)->get()->count() > 0) {
                $order_no = $model->getRandomChar(12);
            }

            $model->order_no = $order_no;
        });
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
