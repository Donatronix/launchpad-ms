<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sumra\SDK\Traits\UuidTrait;

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
