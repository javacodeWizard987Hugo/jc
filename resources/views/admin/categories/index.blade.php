@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="page-header">
        <div class="flex justify-between items-center">
            <div>
                <h1>Categories</h1>
                <p>Organize your products into categories</p>
            </div>
            <a href="{{ route(($routePrefix ?? 'admin') . '.categories.create') }}" class="btn btn-primary bg-white text-red-600 hover:bg-gray-100">
                <span class="mr-2">➕</span> Add Category
            </a>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <div class="content-card p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">📁</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg text-gray-900">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->items_count }} items</p>
                    </div>
                </div>
                @if($category->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </div>
            @if($category->description)
            <p class="text-sm text-gray-600 mb-4">{{ \Illuminate\Support\Str::limit($category->description, 100) }}</p>
            @endif
            <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                <a href="{{ route(($routePrefix ?? 'admin') . '.categories.edit', $category) }}" class="btn btn-secondary flex-1 text-sm">Edit</a>
                <form action="{{ route(($routePrefix ?? 'admin') . '.categories.destroy', $category) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this category?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-full text-sm">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="content-card p-12 text-center">
                <div class="text-6xl mb-4">📁</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Categories</h3>
                <p class="text-gray-500 mb-6">Create your first category to organize your products</p>
                <a href="{{ route(($routePrefix ?? 'admin') . '.categories.create') }}" class="btn btn-primary">Create Category</a>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
