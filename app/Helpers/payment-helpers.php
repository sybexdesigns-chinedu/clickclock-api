<?php

use App\Models\Transaction;
use App\Models\Subscription;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

function activateSubscription($subscription_id)
{
    $subscription = Subscription::where('subscription_id', $subscription_id)->first();
    if (!$subscription) {
        Log::error('Subscription not found', ['id' => $subscription_id]);
        return false;
    }
    $interval = $subscription->plan->interval;
    $subscription->end = now()->addDays($interval);
    $subscription->start = now();
    $subscription->status = SubscriptionStatus::Active->value;
    $subscription->user->is_subscribed = true;
    $subscription->push();
    return true;
}

function suspendSubscription($subscription_id)
{
    $subscription = Subscription::where('subscription_id', $subscription_id)->first();
    if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
    $subscription->update([
        'status' => SubscriptionStatus::Suspended->value,
        'auto_renew' => false
    ]);
    return response()->json(['message' => 'Auto renewal disabled']);
}

function reactivateSubscription($subscription_id)
{
    $subscription = Subscription::where('subscription_id', $subscription_id)->first();
    if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
    $subscription->update([
        'status' => SubscriptionStatus::Active->value,
        'auto_renew' => true
    ]);
    return response()->json(['message' => 'Auto renewal enabled']);
}

function cancelSubscription($subscription_id)
{
    $subscription = Subscription::where('subscription_id', $subscription_id)->first();
    if (!$subscription) return response()->json(['message' => 'Subscription not found'], 404);
    $subscription->update([
        'status' => SubscriptionStatus::Cancelled->value,
        'auto_renew' => false
    ]);
    return response()->json(['message' => 'Auto renewal disabled']);
}

function expireSubscription($subscription_id)
{
    $provider = new PayPalClient();
    $provider->getAccessToken();
    $provider->cancelSubscription($subscription_id, 'Cancelled by user');
    $subscription = Subscription::where('subscription_id', $subscription_id)->first();
    if (!$subscription) return false;
    $subscription->update([
        'status' => SubscriptionStatus::Expired->value,
        'auto_renew' => false
    ]);
    $subscription->user->is_subscribed = false;
    $subscription->user->push();
    return true;
}

function approvePayment($transaction_ref)
{
    $transaction = Transaction::where('reference', $transaction_ref)->first();
    if (!$transaction) return response()->json(['message' => 'Transaction not found'], 404);
    if ($transaction->title === 'Verification Badge') {
        $transaction->user->badge_status = 'paid';
        $transaction->status = 'APPROVED';
        $transaction->push();
    }
    return response()->json(['message' => 'Payment approved']);
}
