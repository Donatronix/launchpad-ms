<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TokenReward extends Model
{

    protected $table = 'token_rewards';

    protected $fillable = [
        'id',
        'purchase_band',
        'swap',
        'deposit_amount',
        'reward_bonus',
    ];


    public static function boot()
    {
        parent::boot();
        self::saving(function ($tokenReward) {
            if (empty($tokenReward->id)) {
                $tokenReward->id = Str::uuid();
            }
        });
    }


}
