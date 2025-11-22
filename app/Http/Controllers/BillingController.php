<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Payment\PaymentContext;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function history()
    {
        $payments = auth()->user()->payments()
            ->with('subscription')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('dashboard.billing.history', compact('payments'));
    }

    public function downloadInvoice(Payment $payment)
    {
        $this->authorize('view', $payment);

        $stripe = new StripeClient(config('services.stripe.secret'));

        try {
            // Get the invoice from Stripe
            $invoices = $stripe->invoices->all([
                'customer' => auth()->user()->stripe_customer_id,
                'limit' => 100,
            ]);

            $invoice = collect($invoices->data)->first(function ($inv) use ($payment) {
                return $inv->payment_intent === $payment->stripe_payment_id;
            });

            if ($invoice && $invoice->invoice_pdf) {
                return redirect($invoice->invoice_pdf);
            }

            return back()->with('error', 'Invoice not available for download.');
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to retrieve invoice.');
        }
    }

    public function updatePaymentMethod()
    {
        $payment = new PaymentContext('stripe');
        $url = $payment->createBillingPortalSession(
            auth()->user(),
            route('billing.history')
        );

        return redirect($url);
    }
}
