<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentContext;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        $payment = new PaymentContext('stripe');
        $result = $payment->handleWebhook($payload, $signature);

        if (!$result->success) {
            return response()->json(['error' => $result->message], 400);
        }

        return response()->json(['status' => 'success']);
    }
}
