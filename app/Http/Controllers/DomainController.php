<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Page;
use App\Services\DomainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function __construct(
        protected DomainService $domainService
    ) {}

    public function index(): View
    {
        $domains = auth()->user()->domains()->with('page')->latest()->get();

        return view('dashboard.domains.index', compact('domains'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain',
            'page_id' => 'required|exists:pages,id',
        ]);

        $page = Page::where('id', $validated['page_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $domain = $this->domainService->create($page, $validated['domain']);

        return response()->json([
            'success' => true,
            'message' => 'Domain added successfully',
            'domain' => $domain,
        ]);
    }

    public function verify(Domain $domain): JsonResponse
    {
        $this->authorize('update', $domain);

        $result = $this->domainService->verify($domain);

        return response()->json([
            'success' => $result['verified'],
            'message' => $result['message'],
            'domain' => $domain->fresh(),
        ]);
    }

    public function destroy(Domain $domain): JsonResponse
    {
        $this->authorize('delete', $domain);

        $domain->delete();

        return response()->json([
            'success' => true,
            'message' => 'Domain removed successfully',
        ]);
    }

    public function checkSsl(Domain $domain): JsonResponse
    {
        $this->authorize('view', $domain);

        $result = $this->domainService->checkSsl($domain);

        return response()->json([
            'success' => true,
            'ssl_status' => $result['status'],
            'message' => $result['message'],
        ]);
    }
}
