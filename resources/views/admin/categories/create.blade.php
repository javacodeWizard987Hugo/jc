@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Category</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route(($routePrefix ?? 'admin') . '.categories.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('name') border-red-500 @enderror"
                       value="{{ old('name') }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route(($routePrefix ?? 'admin') . '.categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

