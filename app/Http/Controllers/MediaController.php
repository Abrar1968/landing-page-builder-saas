<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        return view('dashboard.media.index', [
            'storageUsed' => $this->mediaService->getUserStorageUsed($user),
            'storageLimit' => $this->mediaService->getStorageLimit($user),
        ]);
    }
}
