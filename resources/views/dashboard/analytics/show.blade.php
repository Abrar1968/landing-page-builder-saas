@extends('layouts.dashboard')

@section('title', 'Analytics - ' . $page->title)

@section('content')
<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('analytics.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Analytics</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-sm text-gray-900">{{ $page->title }}</li>
                </ol>
            </nav>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $page->title }}</h1>
        </div>
        <div class="mt-4 sm:mt-0">
            <select id="dateRange" class="rounded-md border-gray-300 text-sm">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Total Views</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['total_views']) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Unique Visitors</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['unique_visitors']) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Views Today</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['views_today']) }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">Views This Week</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ number_format($stats['views_this_week']) }}</dd>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Views Over Time -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-lg font-medium text-gray-900">Views Over Time</h3>
            <div class="mt-4" style="height: 300px;">
                <canvas id="viewsChart"></canvas>
            </div>
        </div>

        <!-- Device Breakdown -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-lg font-medium text-gray-900">Devices</h3>
            <div class="mt-4" style="height: 300px;">
                <canvas id="devicesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Browser Breakdown -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-lg font-medium text-gray-900">Browsers</h3>
            <div class="mt-4" style="height: 300px;">
                <canvas id="browsersChart"></canvas>
            </div>
        </div>

        <!-- Top Referrers -->
        <div class="rounded-lg bg-white p-6 shadow">
            <h3 class="text-lg font-medium text-gray-900">Top Referrers</h3>
            <div class="mt-4">
                @if($referrers->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($referrers as $referrer)
                            <li class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-900 truncate">{{ $referrer->domain }}</span>
                                <span class="text-sm font-medium text-gray-500">{{ number_format($referrer->count) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">No referrer data yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Views Chart
    const viewsCtx = document.getElementById('viewsChart').getContext('2d');
    new Chart(viewsCtx, {
        type: 'line',
        data: {
            labels: @json($viewsByDay->pluck('date')),
            datasets: [{
                label: 'Views',
                data: @json($viewsByDay->pluck('views')),
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Unique Visitors',
                data: @json($viewsByDay->pluck('unique_visitors')),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Devices Chart
    const devicesCtx = document.getElementById('devicesChart').getContext('2d');
    new Chart(devicesCtx, {
        type: 'doughnut',
        data: {
            labels: @json($devices->pluck('device_type')),
            datasets: [{
                data: @json($devices->pluck('count')),
                backgroundColor: [
                    'rgb(79, 70, 229)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Browsers Chart
    const browsersCtx = document.getElementById('browsersChart').getContext('2d');
    new Chart(browsersCtx, {
        type: 'bar',
        data: {
            labels: @json($browsers->pluck('browser')),
            datasets: [{
                label: 'Views',
                data: @json($browsers->pluck('count')),
                backgroundColor: 'rgb(79, 70, 229)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
@endsection
