<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sumra\SDK\Traits\UuidTrait;

class TokenReward extends Model
{
    use UuidTrait;

    protected $fillable = [
        'purchase_band',
        'swap',
        'deposit_amount',
        'reward_bonus',
    ];
}
