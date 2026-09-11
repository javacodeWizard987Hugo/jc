@extends('layouts.app')

@section('title', 'Sales Growth Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Sales Growth Report</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.sales-growth', ['format' => 'pdf']) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                📥 Download PDF
            </a>
            <a href="{{ route('admin.reports.sales-growth-print', ['print' => 1]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold">
                🖨️ Print Report
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Daily Comparison -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-black text-center">Daily Comparison</h3>
            <div class="flex justify-around items-center h-48">
                <div class="text-center">
                    <p class="text-sm text-gray-500 font-bold uppercase">Yesterday</p>
                    <p class="text-2xl font-bold text-gray-700">Rs. {{ number_format($yesterdaySales, 2) }}</p>
                </div>
                <div class="text-4xl text-gray-300">vs</div>
                <div class="text-center">
                    <p class="text-sm text-gray-500 font-bold uppercase">Today</p>
                    <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($todaySales, 2) }}</p>
                    @php
                        $dayDiff = $todaySales - $yesterdaySales;
                        $dayPercent = $yesterdaySales > 0 ? ($dayDiff / $yesterdaySales) * 100 : 100;
                    @endphp
                    <span class="text-sm font-bold {{ $dayDiff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $dayDiff >= 0 ? '▲' : '▼' }} {{ number_format(abs($dayPercent), 1) }}%
                    </span>
                </div>
            </div>
            <div class="mt-4">
                <canvas id="dailyComparisonChart"></canvas>
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-black text-center">Monthly Comparison</h3>
            <div class="flex justify-around items-center h-48">
                <div class="text-center">
                    <p class="text-sm text-gray-500 font-bold uppercase">Last Month</p>
                    <p class="text-2xl font-bold text-gray-700">Rs. {{ number_format($lastMonthSales, 2) }}</p>
                </div>
                <div class="text-4xl text-gray-300">vs</div>
                <div class="text-center">
                    <p class="text-sm text-gray-500 font-bold uppercase">This Month</p>
                    <p class="text-2xl font-bold text-red-600">Rs. {{ number_format($thisMonthSales, 2) }}</p>
                    @php
                        $monthDiff = $thisMonthSales - $lastMonthSales;
                        $monthPercent = $lastMonthSales > 0 ? ($monthDiff / $lastMonthSales) * 100 : 100;
                    @endphp
                    <span class="text-sm font-bold {{ $monthDiff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $monthDiff >= 0 ? '▲' : '▼' }} {{ number_format(abs($monthPercent), 1) }}%
                    </span>
                </div>
            </div>
            <div class="mt-4">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4 text-black">Sales Trend (Last 30 Days)</h3>
        <div class="h-80">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-black">Weekly Sales (Last 4 Weeks)</h3>
            <div class="h-64">
                <canvas id="weeklySalesChart"></canvas>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-black">Revenue Distribution</h3>
            <div class="h-64 flex justify-center">
                <canvas id="revenuePieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Comparison Bar Chart
    new Chart(document.getElementById('dailyComparisonChart'), {
        type: 'bar',
        data: {
            labels: ['Yesterday', 'Today'],
            datasets: [{
                label: 'Revenue',
                data: [{{ $yesterdaySales }}, {{ $todaySales }}],
                backgroundColor: ['#9ca3af', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Monthly Comparison Bar Chart
    new Chart(document.getElementById('monthlyComparisonChart'), {
        type: 'bar',
        data: {
            labels: ['Last Month', 'This Month'],
            datasets: [{
                label: 'Revenue',
                data: [{{ $lastMonthSales }}, {{ $thisMonthSales }}],
                backgroundColor: ['#9ca3af', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // Sales Trend Line Chart
    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($dailySales->pluck('date')) !!},
            datasets: [{
                label: 'Daily Revenue',
                data: {!! json_encode($dailySales->pluck('revenue')) !!},
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Weekly Sales Chart
    new Chart(document.getElementById('weeklySalesChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($weeklySales->pluck('week')->map(fn($w) => "Week $w")) !!},
            datasets: [{
                label: 'Weekly Revenue',
                data: {!! json_encode($weeklySales->pluck('revenue')) !!},
                backgroundColor: '#3b82f6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Revenue Distribution Pie Chart
    new Chart(document.getElementById('revenuePieChart'), {
        type: 'pie',
        data: {
            labels: ['Today', 'Other (Last 30 days)'],
            datasets: [{
                data: [{{ $todaySales }}, {{ $dailySales->sum('revenue') - $todaySales }}],
                backgroundColor: ['#ef4444', '#e5e7eb']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@endsection
