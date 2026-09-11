@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">

    <div class="bg-white shadow rounded-lg p-6">
        <!-- Page Title -->
        <h1 class="text-xl font-semibold text-red-600 mb-6">
            Create Warranty Job
        </h1>

        <form action="{{ route('admin.warranty-jobs.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Warranty -->
            <div>
                <label for="warranty_id"
                    class="block text-sm font-semibold text-red-600 mb-1">
                    Warranty <span class="text-red-500">*</span>
                </label>
                <select name="warranty_id" id="warranty_id"
                    class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200"
                    required>
                    <option value="">Select a Warranty</option>
                    @foreach($warranties as $warranty)
                        <option value="{{ $warranty->id }}">
                            {{ $warranty->serialNumber->item->name }}
                            ({{ $warranty->serialNumber->serial_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Branch -->
            <div>
                <label for="branch_id"
                    class="block text-sm font-semibold text-red-600 mb-1">
                    Branch <span class="text-red-500">*</span>
                </label>
                <select name="branch_id" id="branch_id"
                    class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200"
                    required>
                    <option value="">Select a Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Problem Description -->
            <div>
                <label for="problem_description"
                    class="block text-sm font-semibold text-red-600 mb-1">
                    Problem Description <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="problem_description"
                    id="problem_description"
                    rows="4"
                    class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200 resize-none"
                    placeholder="Describe the customer reported issue..."
                    required></textarea>
            </div>

            <!-- Claim Type -->
            <div>
                <label for="claim_type"
                    class="block text-sm font-semibold text-red-600 mb-1">
                    Claim Type <span class="text-red-500">*</span>
                </label>
                <select name="claim_type" id="claim_type"
                    class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200"
                    required>
                    <option value="repair">Repair</option>
                    <option value="replacement">Replacement</option>
                    <option value="inspection">Inspection</option>
                </select>
            </div>

            <!-- Remarks -->
            <div>
                <label for="remarks"
                    class="block text-sm font-semibold text-red-600 mb-1">
                    Remarks (Optional)
                </label>
                <textarea
                    name="remarks"
                    id="remarks"
                    rows="3"
                    class="w-full rounded-md border-gray-300 focus:border-red-500 focus:ring focus:ring-red-200 resize-none"
                    placeholder="Any additional notes..."></textarea>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                    Create Job
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
