<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class VerifyPaypalWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->validatePaypalWebhook($request)) {
            Log::warning('Invalid PayPal Webhook!');
            return response()->json(['error' => 'Invalid webhook'], 400);
        }
        return $next($request);
    }

    private function validatePaypalWebhook(Request $request)
    {
        $client = new Client();
        $provider = new PayPalClient();

        $headers = $request->header();
        $body = $request->getContent();

        $verifyUrl = "https://api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature"; // Use live URL in production.

        $response = $client->post($verifyUrl, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $provider->getAccessToken()['access_token'],
            ],
            'json' => [
                'transmission_id'   => $headers['paypal-transmission-id'][0] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'][0] ?? '',
                'cert_url'          => $headers['paypal-cert-url'][0] ?? '',
                'auth_algo'         => $headers['paypal-auth-algo'][0] ?? '',
                'transmission_sig'  => $headers['paypal-transmission-sig'][0] ?? '',
                'webhook_id'        => env('PAYPAL_SUBSCRIPTION_WEBHOOK_ID'), // Set this in .env
                'webhook_event'     => json_decode($body, true),
            ]
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        return isset($result['verification_status']) && $result['verification_status'] === 'SUCCESS';
    }
}
