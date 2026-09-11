@extends('layouts.app')

@section('title', 'Customer History Search')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-xl font-bold text-blue-600">
                        Customer History Search
                    </h3>
                </div>
                <div class="card-body">
                   <form action="{{ route('admin.customer-history.search') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Enter NIC or Phone Number</label>
                        <input type="text"
                            name="keyword"
                            class="form-control"
                            placeholder="Enter NIC or Phone Number"
                            required>
                    </div>

                    <button type="submit" class="btn btn-primary">Search</button>
                </form>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
