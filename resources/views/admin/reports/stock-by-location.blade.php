@extends('layouts.admin')

@section('content')
    <h1>Stock by Location</h1>
    <form action="{{ route('admin.reports.stock-by-location') }}" method="GET">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="branch_id">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="item_id">Item</label>
                    <select name="item_id" id="item_id" class="form-control">
                        <option value="">All Items</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}" {{ $itemId == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Branch</th>
                <th>Category</th>
                <th>Item</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stock as $stockItem)
                <tr>
                    <td>{{ $stockItem->branch->name }}</td>
                    <td>{{ $stockItem->item->category->name }}</td>
                    <td>{{ $stockItem->item->name }}</td>
                    <td>{{ $stockItem->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
