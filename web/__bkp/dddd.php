<?php

use App\Models\CreditCardType;
use App\Models\PaymentType;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class dddd
{
    /**
     * @param $transaction_id
     * @return \Illuminate\Http\Response
     */
    public function generatePdfForTransaction($transaction_id)
    {
        try {
            $transaction = Transaction::findOrFail($transaction_id);

            $deposit = $transaction->deposit;

            $transaction->date = $transaction->created_at->toDayDateTimeString();

            if ($transaction->payment_type_id == PaymentType::DEBIT_CARD) {
                $pdf = PDF::loadView('pdf.receipt.deposit-card', $transaction->toArray());

                return $pdf->download('pdf.receipt.deposit-card');

            } elseif (
                $transaction->payment_type_id == PaymentType::CRYPTO
                || $transaction->payment_type_id == PaymentType::FIAT
            ) {
                $pdf = PDF::loadView('pdf.receipt.deposit-wallet', $transaction->toArray());

                return $pdf->download('pdf.receipt.deposit-wallet');
            }

            return response()->jsonApi([
                'title' => 'Deposit details data',
                'message' => "Deposit detail data has been received",
                'data' => $transaction->toArray()
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => "Get deposit",
                'message' => "Transaction with id #{$transaction_id} not found: {$e->getMessage()}",
            ], 404);
        }
    }

   // $router->get('/get-pdf/{transaction_id}', 'DepositController@generatePdfForTransaction');



    /**
     * One Transaction have One Credit Card Type relation
     *
     * @return BelongsTo
     */
    public function creditCardType()
    {
        return $this->belongsTo(CreditCardType::class, 'credit_card_type_id', 'id');
    }

    /**
     * Auto relations for transaction Model
     */

    protected $with = ['creditCardType'];

    // $table->unsignedTinyInteger('credit_card_type_id')->default(0);



//$table->unsignedTinyInteger('payment_type_id');  // fiat/crypto ID
//$table->foreign('payment_type_id')->references('id')->on('payment_types');


    /**
     * One Transaction have One Payment Type relation
     *
     * @return BelongsTo
     */
    public function payment_type()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id', 'id');
    }

//'payment_type_id' => $params['transaction_type_id'],
}
