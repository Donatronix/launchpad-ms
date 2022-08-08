<?php

namespace App\Listeners\PaymentUpdate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Sumra\SDK\Facades\PubSub;

/**
 * Class PurchaseUpdateHandler
 *
 * @package App\Listeners
 */
class PurchaseUpdateHandler
{
    /**
     * @param $document
     */
    public static function exec($document){
        // Get product
        $product = $document->product;

        // Send request to wallet for add token to user
        PubSub::publish('PurchaseToken', [
            'type' => 'charge',
            'amount' => $document->total_token,
            'token' => $product->ticker,
            'user_id' => $document->user_id,
            'document_id' => $document->id,
            'document_object' => 'Purchase',
        ], config('pubsub.queue.crypto_wallets'));
    }
}
