<?php

use App\Models\Transaction;
function approvePayment($transaction_ref)
{
    $transaction = Transaction::where('reference', $transaction_ref)->first();
    if (!$transaction) return response()->json(['message' => 'Transaction not found'], 404);
    if ($transaction->coins) {
        $transaction->user->increment('coins', $transaction->coins);
    }
    $transaction->status = 'APPROVED';
    $transaction->save();
    return response()->json(['message' => 'Payment approved']);
}
