<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Product Price Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="ProductPrice",
 *
 *     @OA\Property(
 *         property="stage",
 *         type="integer",
 *         description="Stage number",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="double",
 *         description="Price per token",
 *         example="0.005"
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="integer",
 *         description="Amount tokens supply",
 *         example="1000000"
 *     ),
 *     @OA\Property(
 *         property="period_in_days",
 *         type="integer",
 *         description="Period in days",
 *         example="10"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="boolean",
 *         description="Status On / Off",
 *         example="true"
 *     )
 * )
 */
class Price extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'stage',
        'price',
        'amount',
        'product_id',
        'period_in_days',
        'percent_profit',
        'status',
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

    public static function validationRules()
    {
        return [
            'price' => 'required',
            'amount' => 'required',
            'product_id' => 'required',
            'stage' => 'required',
            'period_in_days' => 'required',
            'percent_profit' => 'required',
            'status' => 'required',
        ];
    }
}
