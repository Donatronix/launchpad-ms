<?php

namespace App\Models;

use Sumra\SDK\Traits\NumeratorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Purchase Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Purchase",
 *
 *     @OA\Property(
 *         property="product_id",
 *         type="string",
 *         description="Product ID",
 *         example="9a778e5d-61aa-4a2b-b511-b445f6a67909"
 *     ),
 *     @OA\Property(
 *         property="payment amount",
 *         type="integer",
 *         description="Amount to pay",
 *         example="5000"
 *     ),
 *     @OA\Property(
 *         property="currency_ticker",
 *         type="string",
 *         description="currency to pay with",
 *         example="btc/usd/eur"
 *     ),
 *     @OA\Property(
 *         property="currency_type",
 *         type="string",
 *         description="Type of currency. Either Fiat or Crypto",
 *         example="fiat/crypto"
 *     ),
 * )
 */
class Purchase extends Model
{
    use HasFactory;
    use NumeratorTrait;
    use SoftDeletes;
    use UuidTrait;

    /**
     * Get the numerator prefix for the model.
     *
     * @return string
     */
    protected function getNumeratorPrefix(): string
    {
        return 'PR';
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'currency_type',
        'currency_ticker',
        'token_amount',
        'payment_amount',
        'product_id',
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
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
