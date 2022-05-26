<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    const FIAT = 1;
    const CRYPTO = 2;
    const DEBIT_CARD = 3;
}
