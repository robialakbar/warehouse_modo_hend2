@extends('layouts.main')
@section('title', __('Order SYNC List'))
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
@endsection
@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('orders.sync.all') }}" class="btn btn-primary" onclick="return confirm('Yakin ingin sinkronisasi semua order?')">
                <i class="fas fa-sync"></i> Sinkronisasi Semua
            </a>
            <a href="{{ route('orders.sync.purcase_price') }}" class="btn btn-warning" onclick="return confirm('Yakin ingin sinkronisasi semua order?')">
                <i class="fas fa-sync"></i> Sinkronisasi Purcase Price Semua
            </a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>no invoice</th>
                        <th>Total di Order</th>
                        <th>Total dari Detail</th>
                        <th>Selisih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $calculatedTotal = $order->details->sum(function($detail) {
                                return $detail->price * $detail->amount;
                            });
                            // dd($order->details, $calculatedTotal);
                            $difference = $calculatedTotal - $order->total_harga - $order->diskon;
                        @endphp
                        @if($difference != 0)
                        <tr>
                            <td>{{ $order->order_id }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->no_invoice }}</td>
                            <td>{{ number_format($order->total_harga, 0, ',', '.') }}</td>
                            <td>{{ number_format($calculatedTotal, 0, ',', '.') }}</td>
                            <td class="{{ $difference != 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($difference, 0, ',', '.') }}
                            </td>
                            <td>
                                <a href="{{ route('orders.sync.single', $order->order_id) }}" class="btn btn-sm btn-warning" onclick="return confirm('Yakin ingin sinkronisasi order ini?')">
                                    <i class="fas fa-sync"></i> Sinkronisasi
                                </a>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
@section('custom-js')
    <script>
        console.log('Halaman sinkronisasi order loaded.');
    </script>
@stop
