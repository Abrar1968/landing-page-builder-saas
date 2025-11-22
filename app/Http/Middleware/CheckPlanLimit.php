<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        $limits = $this->subscriptionService->getPlanLimits($user);

        switch ($feature) {
            case 'pages':
                if ($limits['pages'] !== -1 && $user->pages()->count() >= $limits['pages']) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Page limit reached',
                            'message' => 'Please upgrade your plan to create more pages.',
                            'upgrade_url' => route('subscription.pricing'),
                        ], 403);
                    }
                    return redirect()->route('subscription.pricing')
                        ->with('error', 'You have reached your page limit. Please upgrade to create more pages.');
                }
                break;

            case 'storage':
                $fileSize = $request->file('file')?->getSize() ?? 0;
                if (!$this->subscriptionService->canUploadMedia($user, $fileSize)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Storage limit reached',
                            'message' => 'Please upgrade your plan for more storage.',
                            'upgrade_url' => route('subscription.pricing'),
                        ], 403);
                    }
                    return redirect()->back()
                        ->with('error', 'You have reached your storage limit. Please upgrade for more storage.');
                }
                break;

            case 'custom_domains':
                if ($limits['custom_domains'] === 0) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Custom domains not available',
                            'message' => 'Please upgrade to Pro or Business for custom domains.',
                            'upgrade_url' => route('subscription.pricing'),
                        ], 403);
                    }
                    return redirect()->route('subscription.pricing')
                        ->with('error', 'Custom domains are not available on your current plan. Please upgrade.');
                }

                if ($limits['custom_domains'] !== -1 && $user->domains()->count() >= $limits['custom_domains']) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Domain limit reached',
                            'message' => 'Please upgrade your plan for more custom domains.',
                            'upgrade_url' => route('subscription.pricing'),
                        ], 403);
                    }
                    return redirect()->route('subscription.pricing')
                        ->with('error', 'You have reached your custom domain limit. Please upgrade.');
                }
                break;
        }

        return $next($request);
    }
}
