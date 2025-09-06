@extends('layouts.main')
@section('title', __('Order List'))
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
@endsection
@section('content')
    <div class="content-header">
        <div class="container-fluid">
        <div class="row mb-2">
        </div>
        </div>
    </div>
    <section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-tools">
                    <form>
                        <div class="input-group input-group">
                            <input type="text" class="form-control" name="q" placeholder="Search">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table id="table" class="table table-sm table-bordered table-hover table-striped">
                <thead>
                    <tr class="text-center">
                        <th>Order ID</th>
                        <th>Tanggal Order</th>
                        <th>Jenis Order</th>
                        <th>No. Invoice</th>
                        <th>Nama Customer</th>
                        <th>No.HP</th>
                        <th>No. Polisi</th>
                        <th>Jenis Kendaraan</th>
                        <th>KM Kendaraan</th>
                        <th>Tahun Kendaraan</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @if(count($orders) > 0)
                    @foreach($orders as $key => $d)
                    <tr>
                        <td class="text-center">{{ $d->order_id }}</td>
                        <td>{{ date("d/m/Y H:i:s", strtotime($d->tgl_order)) }}</td>
                        <td>{{ $d->order_type == 0 ? "Hanya Beli Parts":"Servis & Penggantian Parts" }}</td>
                        <td>{{ $d->no_invoice ? $d->no_invoice:"-" }}</td>
                        <td>{{ $d->customer_name ? $d->customer_name:"-" }}</td>
                        <td>{{ $d->customer_nohp ? $d->customer_nohp:"-" }}</td>
                        <td>{{ $d->kendaraan_nopol ? $d->kendaraan_nopol:"-" }}</td>
                        <td>{{ $d->kendaraan_jenis ? $d->kendaraan_jenis:"-" }}</td>
                        <td>{{ $d->kendaraan_km ? $d->kendaraan_km:"-" }}</td>
                        <td>{{ $d->kendaraan_tahun ? $d->kendaraan_tahun:"-" }}</td>
                        <td>{{ ($d->status == 0) ? "Draft" : (($d->status == 1) ? "Proses" : (($d->status == 2) ? "Menunggu Pembayaran" : "Selesai")) }}</td>
                        <td class="text-center" style="width:64px !important;"><button title="Detail" type="button" class="btn btn-primary btn-xs" onclick="window.location.href='{{ route('order') }}?id={{ $d->order_id }}'"><i class="fa fa-info-circle"></i></button>@if($d->status != 0) <button title="Batalkan" type="button" class="btn btn-danger btn-xs" onclick="window.location.href='{{ route('order.cancel') }}?id={{ $d->order_id }}'"><i class="fa fa-close"></i></button>@endif</td>
                    </tr>
                    @endforeach
                @else
                    <tr class="text-center">
                        <td colspan="11">{{ __('No order.') }}</td>
                    </tr>
                @endif
                </tbody>
                </table>
            </div>
        </div>
        <div>
        {{ $orders->links("pagination::bootstrap-4") }}
        </div>
    </div>
</section>
@endsection
@section('custom-js')
    <script>
        
    </script>
    <script src="/plugins/toastr/toastr.min.js"></script>
    @if(Session::has('success'))
        <script>toastr.success('{!! Session::get("success") !!}');</script>
    @endif
    @if(Session::has('error'))
        <script>toastr.error('{!! Session::get("error") !!}');</script>
    @endif
    @if(!empty($errors->all()))
        <script>toastr.error('{!! implode("", $errors->all("<li>:message</li>")) !!}');</script>
    @endif
@endsection