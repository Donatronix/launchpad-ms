<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    const STATUS_WAITING = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_CANCELED = 0;

    /**
     * @var int[]
     */
    public static $statuses = [
        self::STATUS_CANCELED,
        self::STATUS_WAITING,
        self::STATUS_CONFIRMED
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
        'name',
        'sender_id',
        'receiver_id',
        'amount',
        'currency_id',
        'status'
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
     * Get the owning transactionable model.
     */
    public function transactionable()
    {
        return $this->morphTo();
    }
}
