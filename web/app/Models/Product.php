<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sumra\SDK\Traits\UuidTrait;

class Product extends Model
{
    use HasFactory;
    use UuidTrait;

//s$SLAPA - Synthetic SLAPA Token
//$SLAPA - SLAPA Token
//$DIVIT - DIVIT Token
//$UTTA - UTTA Token

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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
