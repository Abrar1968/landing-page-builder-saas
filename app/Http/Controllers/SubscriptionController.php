<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function pricing()
    {
        $plans = config('subscription.plans');
        $currentPlan = $this->subscriptionService->getCurrentPlan(auth()->user());

        return view('dashboard.subscription.pricing', compact('plans', 'currentPlan'));
    }

    public function checkout(Request $request)
    {
        $request->validate(['plan' => 'required|string']);

        $session = $this->subscriptionService->createCheckoutSession(
            auth()->user(),
            $request->plan
        );

        return redirect($session->url);
    }

    public function success()
    {
        return view('dashboard.subscription.success');
    }

    public function cancel()
    {
        return view('dashboard.subscription.cancel');
    }

    public function manage()
    {
        $user = auth()->user();
        $subscription = $user->subscription;
        $usage = $this->subscriptionService->getUsageStats($user);
        $plans = config('subscription.plans');

        return view('dashboard.subscription.manage', compact('subscription', 'usage', 'plans'));
    }

    public function billingPortal()
    {
        $url = $this->subscriptionService->getBillingPortalUrl(auth()->user());
        return redirect($url);
    }

    public function cancelSubscription()
    {
        $this->subscriptionService->cancel(auth()->user());

        return back()->with('success', 'Your subscription will be canceled at the end of the billing period.');
    }

    public function resumeSubscription()
    {
        $this->subscriptionService->resume(auth()->user());

        return back()->with('success', 'Your subscription has been resumed.');
    }
}
