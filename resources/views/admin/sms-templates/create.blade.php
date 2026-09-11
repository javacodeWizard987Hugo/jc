@extends('layouts.app')

@section('title', 'Create SMS Template')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Create SMS Template</h1>
            <p class="mt-2 text-sm text-gray-700">Create a new SMS template.</p>
        </div>
    </div>
    <div class="mt-8">
        <form action="{{ route('admin.sms-templates.store') }}" method="POST" class="bg-white shadow sm:rounded-lg p-6">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                    <input type="text" name="event_name" id="event_name" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="template" class="block text-sm font-medium text-gray-700">Template</label>
                    <textarea id="template" name="template" rows="4" required class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                </div>
                <div>
                    <label for="placeholders" class="block text-sm font-medium text-gray-700">Placeholders</label>
                    <input type="text" name="placeholders" id="placeholders" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g., CustomerName, Amount, Date">
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
