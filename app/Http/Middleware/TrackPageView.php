<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($page = $request->route('page')) {
            $this->recordView($request, $page);
        }

        return $response;
    }

    protected function recordView(Request $request, $page): void
    {
        $userAgent = $request->userAgent();
        
        PageView::create([
            'page_id' => is_object($page) ? $page->id : $page,
            'visitor_id' => $this->generateVisitorId($request),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'referrer' => $request->header('referer'),
            'device_type' => $this->detectDeviceType($userAgent),
            'browser' => $this->detectBrowser($userAgent),
        ]);
    }

    protected function generateVisitorId(Request $request): string
    {
        return md5($request->ip() . $request->userAgent() . date('Y-m-d'));
    }

    protected function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';
        
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            return preg_match('/ipad|tablet/i', $userAgent) ? 'tablet' : 'mobile';
        }
        
        return 'desktop';
    }

    protected function detectBrowser(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';
        
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';
        if (preg_match('/Opera|OPR/i', $userAgent)) return 'Opera';
        
        return 'Other';
    }
}
