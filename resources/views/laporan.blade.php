@extends('layouts.main')
@section('title', __('Laporan'))
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="/plugins/chart.js/Chart.css">

	<link rel="stylesheet" href="{{ url('/plugins/daterangepicker/daterangepicker.css') }}">
	<link rel="stylesheet" href="{{ url('/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
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
        <div class="row">
            <div style="margin: 20px 0px;">
                <input type="text" name="daterange" value=""/>
                <button class="btn btn-success btn-sm" id="filter" onclick="filter()"><i class="fa fa-filter"></i></button>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fa fa-motorcycle"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Unit Entry</span>
                        <span class="info-box-number">
                        {{ $laporan["unit_entry"] }}
                        </span>
                    </div>
                    </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fa fa-cubes"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Penjualan Parts</span>
                        <span class="info-box-number">
                        {{ $laporan["total_biaya_parts"] }} ({{ $total_qty_parts }})
                        </span>
                    </div>
                </div>
          </div>
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fa fa-usd"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Pendapatan Jasa</span>
                <span class="info-box-number">
                  {{ $laporan["total_biaya_jasa"] }} ({{ $total_qty_jasa }})
                </span>
              </div>
            </div>
          </div>
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fa fa-line-chart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Total Omset</span>
                <span class="info-box-number">
                  {{ $laporan["total_omset"] }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="form-group row col-sm-12">
                    <div class="col-sm-6">
                        <div class="col-sm-9">
                            <form id="jenis_laporan" action="" method="get">
                                <input type="hidden" name="metode_pembayaran" value="{{ Request::get('metode_pembayaran') }}"/>
                                <select class="form-control select2" style="width: 100%;" id="reports" name="reports">
                                    <option value="daily" {{ Request::get('reports') == null || Request::get('reports') == 'daily'? 'selected':'' }}>Harian</option>
                                    <option value="monthly" {{ Request::get('reports') == 'monthly'? 'selected':'' }}>Bulanan</option>
                                    <option value="yearly" {{ Request::get('reports') == 'yearly'? 'selected':'' }}>Tahunan</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-sm-9">
                            <form id="jenis_pembayaran" action="" method="get">
                                <input type="hidden" name="reports" value="{{ Request::get('reports') }}"/>
                                <select class="form-control select2" style="width: 100%;" id="metode_pembayaran" name="metode_pembayaran">
                                    <option value="" {{ Request::get('metode_pembayaran') == null ? 'selected':'' }}>Semua</option>
                                    <option value="0" {{ Request::get('metode_pembayaran') == '0'? 'selected':'' }}>Tunai</option>
                                    <option value="1" {{ Request::get('metode_pembayaran') == '1'? 'selected':'' }}>QRIS</option>
                                    <option value="2" {{ Request::get('metode_pembayaran') == '2'? 'selected':'' }}>Transfer Bank</option>
                                    <option value="3" {{ Request::get('metode_pembayaran') == '3'? 'selected':'' }}>EDC</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-primary" onclick="download('pdf')"><i class="fa fa-file-pdf-o"></i> Download (PDF)</button>
                <button type="button" class="btn btn-primary" onclick="download('xls')"><i class="fa fa-file-excel-o"></i> Download (XLS)</button>
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No.</th>
                                <th>Order ID</th>
                                <th>No. Invoice</th>
                                <th>Tgl. Invoice</th>
                                <th>Jenis Order</th>
                                <th>Nama Customer</th>
                                <th>No. HP</th>
                                <th>No. Polisi</th>
                                <th>Jenis Kendaraan</th>
                                <th>KM Kendaraan</th>
                                <th>Tahun Kendaraan</th>
                                <th>Metode Pembayaran</th>
                                <th>Total Pembelian</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(count($orders) > 0)
                            @foreach($orders as $key => $d)
                            <tr>
                                <td class="text-center">{{ $orders->firstItem() + $key }}</td>
                                <td class="text-center">{{ $d->order_id }}</td>
                                <td class="text-center">{{ $d->no_invoice }}</td>
                                <td class="text-center">{{ date("d/m/Y", strtotime($d->tgl_invoice)) }}</td>
                                <td>{{ $d->order_type == 0 ? "Hanya Beli Parts":"Servis & Penggantian Parts" }}</td>
                                <td>{{ $d->customer_name ? $d->customer_name:"-" }}</td>
                                <td>{{ $d->customer_nohp }}</td>
                                <td>{{ $d->kendaraan_nopol ? $d->kendaraan_nopol:"-" }}</td>
                                <td>{{ $d->kendaraan_jenis ? $d->kendaraan_jenis:"-" }}</td>
                                <td>{{ $d->kendaraan_km ? $d->kendaraan_km:"-" }}</td>
                                <td>{{ $d->kendaraan_tahun ? $d->kendaraan_tahun:"-" }}</td>
                                <td>{{ ($d->metode_pembayaran == 0) ? "Tunai" : (($d->metode_pembayaran == 1) ? "QRIS" : (($d->metode_pembayaran == 2) ? "Transfer Bank" : "EDC")) }}</td>
                                <td class="text-right">{{ number_format($d->total_harga, 0, ",", ".") }}</td>
                                <td class="text-center"><button title="Detail" type="button" class="btn btn-primary btn-xs" onclick="view('{{ route('order') }}?id={{ $d->order_id }}')"><i class="fa fa-info-circle"></i></button></td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="12" class="text-right"><b>Grand Total</b></td>
                                <td class="text-right"><b>{{ $laporan["total_omset"] }}</b></td>
                                <td></td>
                            </tr>
                        @else
                            <tr class="text-center">
                                <td colspan="14">{{ __('No order.') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
        {{ $orders->appends(request()->except('page'))->links("pagination::bootstrap-4") }}
        </div>
    </div>
</section>
@endsection
@section('custom-js')
    <script src="/plugins/toastr/toastr.min.js"></script>
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script src="/plugins/moment/moment.min.js"></script>
    <script src="/plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
    <script src="/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="/plugins/chart.js/Chart.bundle.min.js"></script>
    <script>
        $(function () {
            var user_id;
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            @if(!empty(Request::get('startDate')) && !empty(Request::get('endDate')))
            $('input[name="daterange"]').daterangepicker({
                startDate: "{{ Request::get('startDate') }}",
                endDate: "{{ Request::get('endDate') }}",
                locale: {
                    format: 'YYYY/MM/DD'
                }
            });
            @else
            $('input[name="daterange"]').daterangepicker({
                startDate: moment().startOf('day'),
                endDate: moment(),
                locale: {
                    format: 'YYYY/MM/DD'
                }
            });
            @endif

        });

        $('#reports').on('change', function(e) {
            $("#jenis_laporan").submit();
        });

        $('#metode_pembayaran').on('change', function(e) {
            $("#jenis_pembayaran").submit();
        });

        function download(type){
            view("{{ route('laporan') }}?reports={{ Request::get('reports')}}&startDate={{ Request::get('startDate')}}&endDate={{ Request::get('endDate')}}&format="+type)
        }

        function filter(){
            fd = $('input[name="daterange"]').data('daterangepicker').startDate.format('YYYY-MM-DD');
            td = $('input[name="daterange"]').data('daterangepicker').endDate.format('YYYY-MM-DD');
            view("{{ route('laporan') }}?startDate="+fd+"&endDate="+td, "_self");
        }
    </script>
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