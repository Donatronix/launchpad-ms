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

    /**
     * Get the token reward's deposit amount.
     *
     * @param int $value
     *
     * @return float|int
     */
    public function getDepositAmountAttribute(int $value): float|int
    {
        return $value / 100;
    }

    /**
     * Set the user's first name.
     *
     * @param string $value
     *
     * @return void
     */
    public function setDepositAmountAttribute(string $value)
    {
        $this->attributes['deposit_amount'] = floatval($value) * 100;
    }

}
