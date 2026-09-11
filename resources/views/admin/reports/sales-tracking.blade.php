@extends('layouts.app')

@section('title', 'Sales Tracking Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center print:hidden">
        <h1 class="text-3xl font-bold text-black">Sales Tracking Report</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.reports.sales-tracking', ['day1' => $day1, 'day2' => $day2, 'format' => 'pdf']) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">
                📥 Download PDF
            </a>
            <a href="{{ route('admin.reports.sales-tracking-print', ['day1' => $day1, 'day2' => $day2, 'print' => 1]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-bold">
                🖨️ Print Report
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6 print:hidden">
        <form method="GET" action="{{ route('admin.reports.sales-tracking') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="day1" class="block text-sm font-medium text-gray-700">Select Date 1</label>
                <input type="date" name="day1" value="{{ $day1 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label for="day2" class="block text-sm font-medium text-gray-700">Select Date 2</label>
                <input type="date" name="day2" value="{{ $day2 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 font-bold">Compare Sales</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
            <h3 class="text-lg font-semibold mb-8 text-black">Comparison Analysis</h3>
            <div class="w-full h-80">
                <canvas id="trackingBarChart"></canvas>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-12 text-center w-full">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">{{ $day1 }}</p>
                    <p class="text-2xl font-bold text-gray-700">Rs. {{ number_format($day1Data, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">{{ $day2 }}</p>
                    <p class="text-2xl font-bold text-gray-700">Rs. {{ number_format($day2Data, 2) }}</p>
                </div>
            </div>
            @php
                $diff = $day1Data - $day2Data;
                $percent = $day2Data > 0 ? ($diff / $day2Data) * 100 : 100;
            @endphp
            <div class="mt-8 text-center p-4 rounded-lg {{ $diff >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
                <p class="text-lg font-bold {{ $diff >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ $diff >= 0 ? 'Increase of ' : 'Decrease of ' }} 
                    Rs. {{ number_format(abs($diff), 2) }} 
                    ({{ number_format(abs($percent), 1) }}%)
                </p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 flex flex-col items-center">
            <h3 class="text-lg font-semibold mb-8 text-black">Revenue Contribution</h3>
            <div class="w-full h-80 flex justify-center">
                <canvas id="trackingPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Comparison Bar Chart
    new Chart(document.getElementById('trackingBarChart'), {
        type: 'bar',
        data: {
            labels: ['{{ $day1 }}', '{{ $day2 }}'],
            datasets: [{
                label: 'Total Revenue',
                data: [{{ $day1Data }}, {{ $day2Data }}],
                backgroundColor: ['#ef4444', '#3b82f6'],
                borderColor: ['#b91c1c', '#1d4ed8'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Comparison Pie Chart
    new Chart(document.getElementById('trackingPieChart'), {
        type: 'pie',
        data: {
            labels: ['{{ $day1 }}', '{{ $day2 }}'],
            datasets: [{
                data: [{{ $day1Data }}, {{ $day2Data }}],
                backgroundColor: ['#ef4444', '#3b82f6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@endsection
