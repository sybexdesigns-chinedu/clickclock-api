<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
class WebhookController extends Controller
{
    public function paypalWebhooks(Request $request) {
        $payload = $request->all();
        $subscription_id = $payload['resource']['id'];
        switch ($payload['event_type']) {
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $provider = new PayPalClient();
                $provider->getAccessToken();
                $provider->cancelSubscription($subscription_id, 'Cancelled by user');
                cancelSubscription($subscription_id);
                break;

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                IF($payload['resource']['status_change_note']) {
                    $provider = new PayPalClient();
                    $provider->getAccessToken();
                    $provider->activateSubscription($subscription_id, 'Reactivated by user');
                    reactivateSubscription($subscription_id);
                }
                break;

            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $provider = new PayPalClient();
                $provider->getAccessToken();
                $provider->suspendSubscription($subscription_id, 'Suspended by user');
                suspendSubscription($subscription_id);
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                expireSubscription($subscription_id);
                break;

            case 'PAYMENT.SALE.COMPLETED':
                if ($payload['resource']['billing_agreement_id'])
                    activateSubscription($payload['resource']['billing_agreement_id']);
                break;

            case 'PAYMENT.CAPTURE.COMPLETED';
                $id = $payload['resource']['supplementary_data']['related_ids']['order_id'];
                approvePayment($id);
                break;
        }
        return response()->noContent();
    }

    public function stripeWebhooks(Request $request) {
        $payload = $request->all();
        Log::info('Stripe webhook', $payload);
        return response()->json(['message' => 'Success']);
    }
}
