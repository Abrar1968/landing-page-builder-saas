<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DynamicDataController extends Controller
{
    /**
     * Get available dynamic tags for the builder
     */
    public function getTags(Request $request)
    {
        return response()->json([
            'categories' => [
                'user' => [
                    'label' => 'User',
                    'tags' => [
                        ['id' => 'user.name', 'label' => 'Name', 'value' => Auth::user()?->name ?? 'Guest User'],
                        ['id' => 'user.email', 'label' => 'Email', 'value' => Auth::user()?->email ?? 'guest@example.com'],
                    ]
                ],
                'date' => [
                    'label' => 'Date & Time',
                    'tags' => [
                        ['id' => 'date.now', 'label' => 'Current Date', 'value' => now()->format('Y-m-d')],
                        ['id' => 'time.now', 'label' => 'Current Time', 'value' => now()->format('H:i')],
                    ]
                ],
                'site' => [
                    'label' => 'Site',
                    'tags' => [
                        ['id' => 'site.name', 'label' => 'Site Name', 'value' => config('app.name')],
                        ['id' => 'site.url', 'label' => 'Site URL', 'value' => config('app.url')],
                    ]
                ]
            ]
        ]);
    }
}
