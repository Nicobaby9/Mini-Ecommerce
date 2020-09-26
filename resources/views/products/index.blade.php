@extends('layouts.admin')

@section('title')
    <title>List Product</title>
@endsection

@section('content')
<main class="main">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Home</li>
        <li class="breadcrumb-item active">Product</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                List Product
                                <a href="{{ route('product.uploadExcel') }}" class="btn btn-danger btn-sm">Mass Upload</a>
                                <a href="{{ route('product.create') }}" class="btn btn-primary btn-sm float-right">Add Product</a>
                            </h4>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <form action="{{ route('product.index') }}" method="get">
                                <div class="input-group mb-3 col-md-3 float-right">
                                    <input type="text" name="q" class="form-control" placeholder="Cari..." value="{{ request()->q }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" type="button">Search</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Created At</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($product as $prod)
                                        <tr>
                                            <td>
                                                <img src="{{ asset('storage/products/' . $prod->image) }}" width="150px" height="100px" alt="{{ $prod->name }}">
                                            </td>
                                            <td>
                                                <strong>{{ $prod->name }}</strong><br>
                                                <label>Weight: <span class="badge badge-primary">{{ $prod->weight }} Kg</span></label>
                                                <p>Description : </p>
                                                <strong style="font-weight: 4px;">{!! $prod->description !!}</strong>
                                            </td>
                                            <td>$ {{ number_format($prod->price) }}</td>
                                            <td>{{ $prod->created_at->diffForHumans() }} | {{ $prod->created_at->format('d-M-Y') }}</td>
                                            <td>{!! $prod->status_label !!}</td>
                                            <td>
                                                <form action="{{ route('product.destroy', $prod->id) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="{{ route('product.edit', $prod->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                    <button class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No data.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {!! $product->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection