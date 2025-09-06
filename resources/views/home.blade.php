@extends('layouts.main')
@section('title', __('Dashboard'))
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="/plugins/chart.js/Chart.css">
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
            <div class="col-lg-3 col-6">
                <a href="#" data-toggle="modal" data-target="#stock-form" onclick="stockForm(1)">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <p>Stock</p>
                            <h3>In</h3>
                        </div>
                        <div class="icon">
                            <i class="fa fa-download"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-6">
                <a href="#" data-toggle="modal" data-target="#stock-form" onclick="stockForm(0)">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <p>Stock</p>
                            <h3>Out</h3>
                        </div>
                        <div class="icon">
                            <i class="fa fa-upload"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-6">
                <a href="#" data-toggle="modal" data-target="#stock-form" onclick="stockForm(2)">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <p>Product</p>
                            <h3>Retur</h3>
                        </div>
                        <div class="icon">
                            <i class="fa fa-undo"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-6">
                <a href="{{ route('products.stock.history') }}">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <p>Stock</p>
                            <h3>History</h3>
                        </div>
                        <div class="icon">
                            <i class="fa fa-history"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="card card-primary card-outline">
        <div class="card-header">
        <h3 class="card-title">
            <i class="far fa-chart-bar"></i>
            Barang yang Sering Keluar Selama 30 Hari Terakhir
        </h3>
        </div>
        <div class="card-body">
            <canvas id="chart"></canvas>
        </div>
    </div>
    <div class="modal fade" id="stock-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Stock In') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <img width="150px" src="/img/barcode_scanner.png"/>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="input-group input-group-lg">
                                <input type="text" class="form-control" id="pcode" name="pcode" min="0" placeholder="Product Code">
                                <input type="hidden" id="type" name="type">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="button-check" onclick="productCheck()">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="form" class="card">
                        <div class="card-body">
                            <form role="form" id="stock-update" method="post">
                                @csrf
                                <input type="hidden" id="pid" name="pid">
                                <input type="hidden" id="type" name="type">
                                <div class="form-group row">
                                    <label for="customer" class="col-sm-4 col-form-label">{{ __('Customer') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="customer">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="no_nota" class="col-sm-4 col-form-label">{{ __('No. Nota') }}</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="no_nota">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="amount" class="col-sm-4 col-form-label">{{ __('Jumlah') }}</label>
                                    <div class="col-sm-8">
                                        <input type="number" class="form-control" id="amount" name="amount" min="1" value="1">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
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
            $('.select2').select2({
                theme: 'bootstrap4'
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#pcode').keypress(function (e) {
                var key = e.which;
                if(key == 13){
                    productCheck();
                    return false;  
                }
            });
        });

        function resetForm(){
            $('#pcode').val('');
            $('#no_nota').val('');
            $('#customer').val('');
            $('#amount').val(1);
        }

        function stockForm(type=1){
            resetForm();
            $("#type").val(type);
            if(type == 0){
                $('#modal-title').text("Stock Out");
            } else if(type == 1){
                $('#modal-title').text("Stock In");
            } else {
                $('#modal-title').text("Retur");
            }
        }

        function productCheck(){
            var pcode = $('#pcode').val();
            if(pcode.length > 0){
                $.ajax({
                    url: '/products/check/'+pcode,
                    type: "GET",
                    data: {"format": "json"},
                    dataType: "json",
                    success:function(data) {
                        if(data.status == 1){
                            stockUpdate(data.data);
                        } else {
                            toastr.error("Product Code tidak dikenal!");
                        }
                    }, error:function(){
                        toastr.error("Unknown error! Please try again later!");
                    }
                });
            } else {
                toastr.error("Product Code belum diisi!");
            }
        }

        function stockUpdate(data){
            var data = {
                product_id:data.product_id,
                type:$('#type').val(),
                customer:$('#customer').val(),
                no_nota:$('#no_nota').val(),
                amount:$('#amount').val(),
            }
            
            $.ajax({
                url: '/products/stockUpdate',
                type: "post",
                data: JSON.stringify(data),
                dataType: "json",
                contentType: 'application/json',
                success:function(data) {
                    if(data.status == 1){
                        toastr.success(data.message);
                        resetForm();
                    } else {
                        toastr.error(data.message);
                    }
                }, error:function(){
                    toastr.error("Unknown error! Please try again later!");
                    resetForm();
                }
            });
        }

    const ctx = document.getElementById("chart").getContext('2d');
    @php
        $items = [];
        $jumlah = [];
    @endphp
    @foreach($history as $h)
        @php
            $items[]= $h->product_name;
            $jumlah[]= $h->total;
        @endphp
    @endforeach

    var labels = {!! json_encode($items) !!};
    var data = {!! json_encode($jumlah) !!};
    const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
        label: 'Jumlah Barang Keluar',
        backgroundColor: 'rgba(161, 198, 247, 1)',
        borderColor: 'rgb(47, 128, 237)',
        data: data,
        }]
    },
    options: {
        scales: {
        yAxes: [{
            ticks: {
            beginAtZero: true,
            }
        }]
        }
    },
    });
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