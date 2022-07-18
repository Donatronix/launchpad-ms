<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\UuidTrait;

/**
 * Product Scheme
 *
 * @package App\Models
 *
 * @OA\Schema(
 *     schema="Product",
 *
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Product title",
 *         example="Utta Token"
 *     ),
 *     @OA\Property(
 *         property="ticker",
 *         type="string",
 *         description="Product ticker",
 *         example="utta"
 *     ),
 *     @OA\Property(
 *         property="supply",
 *         type="number",
 *         description="Product supply",
 *         example="100000000000"
 *     ),
 *     @OA\Property(
 *         property="presale_percentage",
 *         type="string",
 *         description="Presale Percentage",
 *         example="0.7"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         description="Start date",
 *         example="7th June 2022"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         description="End date",
 *         example="20th June 2022"
 *     ),
 *     @OA\Property(
 *         property="icon",
 *         type="string",
 *         description="Icon",
 *         example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAC1JREFUWEft0EERAAAAAUH6lxbDZxU4s815PffjAAECBAgQIECAAAECBAgQIDAaPwAh6O5R/QAAAABJRU5ErkJggg=="
 *     )
 * )
 *
 * 
 * Class PossibleLoanAmount
 *
 * @package App\Models
 */
class Product extends Model
{
    use HasFactory;
    use UuidTrait;
    use SoftDeletes;

    //s$SLAPA - Synthetic SLAPA Token
    //$SLAPA - SLAPA Token
    //$DIVIT - DIVIT Token
    //$UTTA - UTTA Token

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'ticker',
        'supply',
        'presale_percentage',
        'start_date',
        'end_date',
        'status',
        'icon',
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
     * Product create rules
     *
     * @return string[]
     */
    public static function validationRules(): array
    {
        return [
            'title' => 'required|string',
            'ticker' => 'required|string|unique:products,ticker',
            'supply' => 'required|integer',
            'presale_percentage' => 'required|string',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'icon' => 'required|string',
        ];
    }

    /**
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * @param int $stage
     * @return HasOne
     */
    public function price(int $stage = 1): HasOne
    {
        return $this->hasOne(Price::class)->where('stage', $stage);
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
