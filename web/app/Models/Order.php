<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'type_id',
        'quantity',
        'buyer_id',
        'status',
        'note'
    ];

    public function types(){
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function users(){
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }
}
