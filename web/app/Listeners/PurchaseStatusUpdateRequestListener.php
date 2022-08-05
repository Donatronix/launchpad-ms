<?php

namespace App\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PubSub;

/**
 * Class PurchaseStatusUpdateRequestListener
 *
 * @package App\Listeners
 */
class PurchaseStatusUpdateRequestListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param array $inputData
     *
     * @return void
     */
    public function handle(array $inputData)
    {
        $validation = Validator::make($inputData, [
            'document_id' => 'string|required',
            'document_object' => 'string|required'
        ]);

        if ($validation->fails()) {
            Log::info('Validation error: ' . $validation->getMessage());

            exit();
        }

        // Init manager
        try {
            // Try read document
            $purchase = app()
                ->make('App\Models\\' . $inputData->document_object)
                ->findOrFail($inputData->document_id);

            $purchase->fill([
                'status' => 23
            ]);
            $purchase->save();

            // send token purchased to wallet
             PubSub::publish('PurchaseToken', [
                 'amount' => $purchase->token_amount,
                 'token' => $purchase->id, // ticker
                 'user_id' => $this->user_id,
             ], config('pubsub.queue.crypto_wallets'));
        } catch (Exception $e) {
            Log::info('Handler data error: ' . $validation->getMessage());
            exit();
        }
    }
}
