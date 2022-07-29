<?php

namespace App\Models;

use App\Traits\NumeratorTrait;
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
 *         property="amount_usd",
 *         type="integer",
 *         description="Amount paid in dollars.",
 *         example="5000"
 *     ),
 *     @OA\Property(
 *         property="crypto",
 *         type="string",
 *         description="Crypto to pay with",
 *         example="btc"
 *     ),
 *     @OA\Property(
 *         property="crypto_amount",
 *         type="integer",
 *         description="Amount of crypto",
 *         example="5"
 *     ),
 *     @OA\Property(
 *         property="currency_type",
 *         type="string",
 *         description="Type of currency. Either Fiat or Crypto",
 *         example="fiat"
 *     ),
 *     @OA\Property(
 *         property="payment_method",
 *         type="string",
 *         description="Method of payment",
 *         example="Credit card"
 *     ),
 *     @OA\Property(
 *         property="payment_status",
 *         type="boolean",
 *         description="Status of payment",
 *         example="true"
 *     )
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
        'amount_usd',
        'crypto',
        'crypto_amount',
        'token_amount',
        'payment_method',
        'product_id',
        'payment_status',
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

    public static function validationRules()
    {
        return [
            'amount_usd' => 'required',
            'token_amount' => 'required',
            'product_id' => 'required',
            'payment_method' => 'required',
            'payment_status' => 'required',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
