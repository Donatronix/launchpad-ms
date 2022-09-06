<?php

use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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


                $pdf = PDF::loadView('pdf.receipt.deposit-card', $transaction->toArray());
                return $pdf->download('pdf.receipt.deposit-card');


                $pdf = PDF::loadView('pdf.receipt.deposit-wallet', $transaction->toArray());
                return $pdf->download('pdf.receipt.deposit-wallet');


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
}
