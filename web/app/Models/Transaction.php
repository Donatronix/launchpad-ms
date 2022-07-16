<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sumra\SDK\Traits\UuidTrait;

class Transaction extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * Transaction type
     */
    const TYPE_CARD = 1;
    const TYPE_CONTRACT = 2;
    const TYPE_PAYMENT_RECHARGE = 3;

    /**
     * Transaction status
     */
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_BONUSES = 3;
    const STATUS_CANCELED = 0;

    /**
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_CANCELED,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_BONUSES
    ];

    /**
     * @var int[]
     */
    public static $types = [
        self::TYPE_CARD,
        self::TYPE_CONTRACT,
        self::TYPE_PAYMENT_RECHARGE
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_type_id',
        'total_amount',
        'order_id',
        'user_id',
        'admin_id',
        'payment_system',
        'credit_card_type_id',
        'wallet_address',
        'currency_code',
        'payment_date',
        'payment_token',
        'token_stage',
        'payment_gateway',
        'bonus',
        'sol_received',
        'amount_received',
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
     * Auto relations for transaction Model
     */

    protected $with = ['creditCardType'];

    /**
     * Get the owning transactionable model.
     */
    public function transactionable()
    {
        return $this->morphTo();
    }

    /**
     * One Transaction have One Order relation
     *
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * One Transaction have One Payment Type relation
     *
     * @return BelongsTo
     */
    public function payment_type()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

    /**
     * One Transaction have One Credit Card Type relation
     *
     * @return BelongsTo
     */
    public function creditCardType()
    {
        return $this->belongsTo(CreditCardType::class, 'credit_card_type_id', 'id');
    }
}
