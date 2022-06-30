<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sumra\SDK\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 *         description="Amount paid in dollars",
 *         example="5000"
 *     ),
 *     @OA\Property(
 *         property="token_amount",
 *         type="double",
 *         description="Amount of token purchased",
 *         example="25312.046"
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
    use UuidTrait;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'amount_usd',
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

    public static function validationRules(){
        return [
            'amount_usd' => 'required',
            'token_amount' => 'required',
            'product_id'=> 'required',
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
