@php
$order_status = $order->status;
@endphp
@extends('layouts.main')
@section('title', __('Order #').$order->order_id)
@section('custom-css')
    <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
                @if($order->status == 3)
                <div class="form-group row">
                    <label for="order_type" class="col-sm-3 col-form-label">Tgl. Invoice</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{ date("d/m/Y", strtotime($order->tgl_invoice)) }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="order_type" class="col-sm-3 col-form-label">No. Invoice</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{ $order->no_invoice }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="order_type" class="col-sm-3 col-form-label">Metode Pembayaran</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{ ($order->metode_pembayaran == 0) ? "Tunai" : (($order->metode_pembayaran == 1) ? "QRIS" : (($order->metode_pembayaran == 2) ? "Transfer Bank" : "EDC")) }}" readonly>
                    </div>
                </div>
                @endif
                <div class="form-group row">
                    <label for="order_type" class="col-sm-3 col-form-label">Jenis Order</label>
                    <div class="col-sm-9">
                        <select class="form-control select2" style="width: 30%;" id="order_type" name="order_type">
                            <option value="0">Hanya Beli Parts</option>
                            <option value="1">Servis & Penggantian Parts</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form role="form" id="customer_data" action="{{ route('order.process') }}" method="post">
                    @csrf
                    <input type="hidden" id="order_id" name="order_id" value="{{ $order->order_id }}">
                    <input type="hidden" id="orderType" name="orderType">
                    <div id="customer_name_input" class="form-group row">
                        <label for="customer_name" class="col-sm-3 col-form-label">Nama</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ $order->customer_name }}">
                        </div>
                    </div>
                    <div id="customer_nohp_input" class="form-group row">
                        <label for="customer_nohp" class="col-sm-3 col-form-label">No. HP.</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="customer_nohp" name="customer_nohp" value="{{ $order->customer_nohp }}">
                        </div>
                    </div>
                    <div id="alamat_input" class="form-group row">
                        <label for="alamat" class="col-sm-3 col-form-label">Alamat</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="alamat" name="alamat" value="{{ $order->customer_alamat }}">
                        </div>
                    </div>
                    <div id="nopol_input" class="form-group row">
                        <label for="nopol" class="col-sm-3 col-form-label">No. Polisi</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nopol" name="nopol" value="{{ $order->kendaraan_nopol }}">
                        </div>
                    </div>
                    <div id="jenis_kendaraan_input" class="form-group row">
                        <label for="jenis_kendaraan" class="col-sm-3 col-form-label">Jenis Kendaraan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="jenis_kendaraan" name="jenis_kendaraan" value="{{ $order->kendaraan_jenis }}">
                        </div>
                    </div>
                    <div id="km_kendaraan_input" class="form-group row">
                        <label for="km_kendaraan" class="col-sm-3 col-form-label">KM Kendaraan</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="km_kendaraan" name="km_kendaraan" value="{{ $order->kendaraan_km }}">
                        </div>
                    </div>
                    <div id="tahun_kendaraan_input" class="form-group row">
                        <label for="tahun_kendaraan" class="col-sm-3 col-form-label">Tahun Kendaraan</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="tahun_kendaraan" name="tahun_kendaraan" value="{{ $order->kendaraan_tahun }}">
                        </div>
                    </div>
                    @if($order_status == 1 || $order_status == 2 || $order_status == 3)
                    <div id="tahun_kendaraan_input" class="form-group row">
                        <label for="nama_mekanik" class="col-sm-3 col-form-label">Nama {{ $order->order_type == 0? "Sales":"Mekanik" }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nama_mekanik" name="nama_mekanik" value="{{ $order->nama_mekanik }}">
                        </div>
                    </div>
                    @endif
                    <div id="catatan_input" class="form-group row">
                        <label for="catatan" class="col-sm-3 col-form-label">Catatan</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="catatan" name="catatan" value="{{ $order->catatan }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                @if($order->status == 0 || $order->status == 1)
                <button type="button" id="addItem" class="btn btn-primary" data-toggle="modal" data-target="#add-item" onclick="addProduct()"><i class="fa fa-cubes"></i> Tambah Parts</button>
                <button type="button" id="addService" class="btn btn-primary" data-toggle="modal" data-target="#add-service" onclick="addService()"><i class="fa fa-wrench"></i> Tambah Jasa Servis</button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No.</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Harga Satuan (Rp)</th>
                                <th>Jumlah</th>
                                <th>Sub Total (Rp)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(count($detail) > 0)
                        @php $i = 1; @endphp
                        @foreach($detail as $key => $m)
                        <tr>
                        <td class="text-center">{{ $i }}</td>
                        <td>{{ ($m->type == 0) ? $m->product_code:"-" }}</td>
                        <td>{{ $m->product_name }}</td>
                        <td class="text-right">{{ number_format($m->sale_price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ $m->amount }}</td>
                        <td class="text-right">{{ number_format($m->sale_price*$m->amount, 0, ',', '.') }}</td>
                        <td class="text-center">@if($order->status == 0 || $order->status == 1)<button title="Hapus Item" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#del-item" onclick="deleteItem({{ json_encode($detail[$loop->iteration-1]) }})"><i class="fa fa-trash"></i></button>@endif</td>
                        </tr>
                        @php $i++; @endphp
                        @endforeach
                        @if($order_status == 2 || $order_status == 3)
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-right"><b>Diskon</b></td>
                            <td class="text-right"><b>{{ number_format($order->diskon, 0, ',', '.') }}</b></td>
                            <td></td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-right"><b>Total</b></td>
                            <td class="text-right"><b>{{ number_format($total_harga, 0, ',', '.') }}</b></td>
                            <td></td>
                        </tr>
                        @if($order_status == 1)
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-right"><b>Diskon</b></td>
                            <td class="text-right"><input type="number" class="form-control" id="diskon" name="diskon" autocomplete="off"></td>
                            <td></td>
                        </tr>
                        @endif
                        @else
                        <tr>
                        <td colspan="7">{{ __('Belum ada item') }}</td>
                        </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                @if(count($detail) > 0 && $order->status == 0)
                <button type="button" class="btn btn-md btn-success pull-right" onclick="proses()"><i class="fa fa-save"></i> Proses</button>
                @endif
                @if($order->status == 1)
                <button type="button" id="selesai" class="btn btn-md btn-success pull-right" onclick="selesai()"><i class="fa fa-check"></i> Selesai</button>
                @endif
                @if($order->status == 2)
                <button type="button" id="pembayaran" class="btn btn-md btn-success pull-right" data-toggle="modal" data-target="#form-pembayaran"><i class="fa fa-money"></i> Pembayaran</button>
                @endif
                @if($order->status == 3)
                <button type="button" class="btn btn-md btn-success pull-right" onclick="reorder()"><i class="fa fa-repeat"></i> Re-order</button>
                <button id="cetakPDF" type="button" class="btn btn-primary" onclick="cetakInvoice('pdf')"><i class="fa fa-file-pdf-o"></i> Cetak Invoice (PDF)</button>
                <button id="cetakJPG" type="button" class="btn btn-primary" onclick="cetakInvoice('jpg')"><i class="fa fa-file-image-o"></i> Cetak Invoice (JPG)</button>
                @endif
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-item">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Tambah Item</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="save" action="{{ route('order.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id" value="{{ $order->order_id }}">
                        <input type="hidden" id="type" name="type" value="0">
                        <input type="hidden" id="orderType2" name="orderType2">
                        <input type="hidden" id="customer_name2" name="customer_name2">
                        <input type="hidden" id="customer_nohp2" name="customer_nohp2">
                        <input type="hidden" id="alamat2" name="alamat2">
                        <input type="hidden" id="nopol2" name="nopol2">
                        <input type="hidden" id="jenis_kendaraan2" name="jenis_kendaraan2">
                        <input type="hidden" id="km_kendaraan2" name="km_kendaraan2">
                        <input type="hidden" id="tahun_kendaraan2" name="tahun_kendaraan2">
                        <input type="hidden" id="catatan2" name="catatan2">
                        <div class="form-group row">
                            <label for="product" class="col-sm-4 col-form-label">Produk</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" style="width: 100%;" id="product" name="product">
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="amount" class="col-sm-4 col-form-label">Jumlah</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" id="amount" name="amount" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="sale_price" class="col-sm-4 col-form-label">Harga Satuan (Rp)</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="sale_price" name="sale_price" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button id="button-save" type="button" class="btn btn-primary" onclick="document.getElementById('save').submit();">{{ __('Tambahkan') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-service">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Tambah Jasa Servis</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="save_service" action="{{ route('order.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id" value="{{ $order->order_id }}">
                        <input type="hidden" id="type" name="type" value="1">
                        <input type="hidden" id="orderType3" name="orderType3">
                        <input type="hidden" id="customer_name3" name="customer_name3">
                        <input type="hidden" id="customer_nohp3" name="customer_nohp3">
                        <input type="hidden" id="alamat3" name="alamat3">
                        <input type="hidden" id="nopol3" name="nopol3">
                        <input type="hidden" id="jenis_kendaraan3" name="jenis_kendaraan3">
                        <input type="hidden" id="km_kendaraan3" name="km_kendaraan3">
                        <input type="hidden" id="tahun_kendaraan3" name="tahun_kendaraan3">
                        <input type="hidden" id="catatan3" name="catatan3">
                        <div class="form-group row">
                            <label for="jasa" class="col-sm-4 col-form-label">Jasa Servis</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" style="width: 100%;" id="jasa" name="jasa">
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="biaya_jasa" class="col-sm-4 col-form-label">Biaya (Rp)</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="biaya_jasa" name="biaya_jasa" readonly>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button id="button-save" type="button" class="btn btn-primary" onclick="document.getElementById('save_service').submit();">{{ __('Tambahkan') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="del-item">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Hapus Item / Jasa</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('order.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="delete_id">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus <span id="delete_name" class="font-weight-bold"></span>?</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                    <button id="button-save" type="button" class="btn btn-danger" onclick="document.getElementById('delete').submit();">{{ __('Ya, hapus') }}</button>
                </div>
            </div>
        </div>
    </div>
    @if($order_status == 2)
    <div class="modal fade" id="form-pembayaran">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Pembayaran</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="order_id" name="order_id" value="{{ $order->order_id }}">
                    <div class="form-group row">
                        <label class="col-sm-5 col-form-label">Total Biaya (Rp)</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="{{ number_format($total_harga, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="jumlah_bayar" class="col-sm-5 col-form-label">Jumlah Pembayaran (Rp)</label>
                        <div class="col-sm-7">
                            <input type="number" class="form-control" id="jumlah_bayar" name="jumlah_bayar">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="metode_pembayaran" class="col-sm-5 col-form-label">Metode Pembayaran</label>
                        <div class="col-sm-7">
                            <select class="form-control select2" style="width: 100%;" id="metode_pembayaran" name="metode_pembayaran" required placeholder="">
                                <option disabled value="" selected>-- Pilih Metode Pembayaran --</option>
                                <option value="0">Tunai</option>
                                <option value="1">QRIS</option>
                                <option value="2">Transfer Bank</option>
                                <option value="3">EDC</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="kembalian" class="col-sm-5 col-form-label">Kembalian (Rp)</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="kembalian" name="kembalian" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button id="button-save" type="button" class="btn btn-success" onclick="pembayaran()">Bayar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection
@section('custom-js')
    <script src="/plugins/toastr/toastr.min.js"></script>
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script src="/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <script>
        $(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.select2').select2({
                theme: 'bootstrap4'
            });

            $("#order_type").val("{{ $order->order_type }}");
            @if($order_status != 0)
            $("#order_type").attr("disabled", true);
            $("#customer_name").attr("readonly", true);
            $("#customer_nohp").attr("readonly", true);
            $("#alamat").attr("readonly", true);
            $("#nopol").attr("readonly", true);
            $("#jenis_kendaraan").attr("readonly", true);
            $("#km_kendaraan").attr("readonly", true);
            $("#tahun_kendaraan").attr("readonly", true);
            $("#catatan").attr("readonly", true);
            @endif
            @if($order_status != 1)
            $("#nama_mekanik").attr("readonly", true);
            @endif
            $("#order_type").change();

            $('#nopol').on('keypress', function (e) {
                if(e.which === 13){
                    cekNopol($("#nopol").val());
                }
            });

        });

        $('#product').on('change', function(e) {
            getDetailProduct();
        });

        $('#jasa').on('change', function(e) {
            getDetailService();
        });

        $('#jumlah_bayar').on('change', function(e) {
            var kembalian = $("#jumlah_bayar").val()-"{{ $total_harga }}";

            if(kembalian < 0){
                $('#kembalian').val("Kurang Bayar: "+separator(kembalian));
            } else {
                $('#kembalian').val(separator(kembalian));
            }
        });

        function separator(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        $('#order_type').on('change', function(e) {
            resetForm();
            orderType = $("#order_type").val();
            if(orderType == null || orderType == "0"){
                $("#customer_name_input").show();
                $("#customer_nohp_input").show();
                $("#alamat_input").show();
                $("#nopol_input").hide();
                $("#jenis_kendaraan_input").hide();
                $("#km_kendaraan_input").hide();
                $("#tahun_kendaraan_input").hide();
                $("#catatan_input").show();
            } else {
                $("#customer_name_input").show();
                $("#customer_nohp_input").show();
                $("#alamat_input").show();
                $("#nopol_input").show();
                $("#jenis_kendaraan_input").show();
                $("#km_kendaraan_input").show();
                $("#tahun_kendaraan_input").show();
                $("#catatan_input").show();
            }
        });

        function getProducts(val){
            $.ajax({
                url: "{{ route('products')}}",
                type: "GET",
                data: {"format": "json"},
                dataType: "json",
                success:function(data) {
                    $('#product').empty();
                    $('#product').append('<option value=""></option>');
                    $.each(data, function(key, value) {
                        if(value.product_id == val){
                            $('#product').append('<option value="'+ value.product_id +'" selected>'+value.product_code+' - '+ value.product_name +'</option>');
                        } else {
                            $('#product').append('<option value="'+ value.product_id +'">'+value.product_code+' - '+ value.product_name +'</option>');
                        }
                    });
                }
            });
        }

        function getServices(val){
            $.ajax({
                url: "{{ route('services')}}",
                type: "GET",
                data: {"format": "json"},
                dataType: "json",
                success:function(data) {
                    $('#jasa').empty();
                    $('#jasa').append('<option value=""></option>');
                    $.each(data, function(key, value) {
                        if(value.service_id == val){
                            $('#jasa').append('<option value="'+ value.service_id +'" selected>'+ value.nama_jasa +'</option>');
                        } else {
                            $('#jasa').append('<option value="'+ value.service_id +'">'+ value.nama_jasa +'</option>');
                        }
                    });
                }
            });
        }

        function selesai(){
            $.ajax({
                url: "{{ route('order.done')}}",
                type: "POST",
                data: {"order_id": "{{ $order->order_id }}", "nama_mekanik": $("#nama_mekanik").val(), "diskon": $("#diskon").val(), "order_type": "{{ $order->order_type }}"},
                dataType: "json",
                success:function(data) {
                    if(data.status == 1){
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                }, error:function(){
                    toastr.error("Unknown error! Please try again later!");
                    resetForm();
                }
            });
        }

        function pembayaran(){
            if($("#metode_pembayaran").val() == null){
                toastr.error("Metode pembayaran belum dipilih!");
                return false;
            }
            $.ajax({
                url: "{{ route('order.payment')}}",
                type: "POST",
                data: {"order_id": "{{ $order->order_id }}", "jumlah_bayar": $("#jumlah_bayar").val(), "metode_pembayaran": $("#metode_pembayaran").val()},
                dataType: "json",
                success:function(data) {
                    if(data.status == 1){
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                }, error:function(){
                    toastr.error("Unknown error! Please try again later!");
                    resetForm();
                }
            });
        }

        function reorder(){
            $.ajax({
                url: "{{ route('order.repeat')}}",
                type: "POST",
                data: {"order_id": "{{ $order->order_id }}"},
                dataType: "json",
                success:function(data) {
                    if(data.status == 1){
                        view(data.url);
                    } else {
                        toastr.error(data.message);
                    }
                }, error:function(){
                    toastr.error("Unknown error! Please try again later!");
                    resetForm();
                }
            });
        }

        function addProduct(){
            $('#modal-title').text("Tambah Item");
            $('#button-save').text("Tambahkan");
            getProducts();
            $("#orderType2").val($("#order_type").val());
            $("#customer_name2").val($("#customer_name").val());
            $("#customer_nohp2").val($("#customer_nohp").val());
            $("#alamat2").val($("#alamat").val());
            $("#nopol2").val($("#nopol").val());
            $("#jenis_kendaraan2").val($("#jenis_kendaraan").val());
            $("#km_kendaraan2").val($("#km_kendaraan").val());
            $("#tahun_kendaraan2").val($("#tahun_kendaraan").val());
            $("#catatan2").val($("#catatan").val());
        }

        function addService(){
            $('#modal-title').text("Tambah Jasa Servis");
            $('#button-save').text("Tambahkan");
            getServices();
            $("#orderType3").val($("#order_type").val());
            $("#customer_name3").val($("#customer_name").val());
            $("#customer_nohp3").val($("#customer_nohp").val());
            $("#alamat3").val($("#alamat").val());
            $("#nopol3").val($("#nopol").val());
            $("#jenis_kendaraan3").val($("#jenis_kendaraan").val());
            $("#km_kendaraan3").val($("#km_kendaraan").val());
            $("#tahun_kendaraan3").val($("#tahun_kendaraan").val());
            $("#catatan3").val($("#catatan").val());
        }

        function getDetailProduct(){
            $.ajax({
                url: "{{ route('products') }}",
                type: "GET",
                data: {"format": "json", "product_id": $("#product").val()},
                dataType: "json",
                success:function(data) {
                    $('#sale_price').val(data.sale_price);
                }
            });
        }

        function getDetailService(){
            $.ajax({
                url: "{{ route('services') }}",
                type: "GET",
                data: {"format": "json", "service_id": $("#jasa").val()},
                dataType: "json",
                success:function(data) {
                    $('#biaya_jasa').val(data.biaya);
                }
            });
        }

        function cekNopol(nopol){
            $.ajax({
                url: "{{ route('check.nopol') }}",
                type: "GET",
                data: {"format": "json", "nopol": nopol},
                dataType: "json",
                success:function(data) {
                    if(data.status == 1){
                        $("#customer_name").val(data.data.customer_name);
                        $("#customer_nohp").val(data.data.customer_nohp);
                        $("#alamat").val(data.data.customer_alamat);
                        $("#jenis_kendaraan").val(data.data.kendaraan_jenis);
                        $("#km_kendaraan").val(data.data.kendaraan_km);
                        $("#tahun_kendaraan").val(data.data.kendaraan_tahun);
                    } else {
                        toastr.error("Data tidak ditemukan!");
                    }
                }
            });
        }

        function resetForm(){
            $('#customer_data').trigger("reset");
        }

        function deleteItem(data){
            $('#delete_id').val(data.order_detail_id);
            $('#delete_name').text(data.product_name);
        }

        function proses(){
            $('#orderType').val($('#order_type').val());
            $('#customer_data').submit();
        }

        function cetakInvoice(type){
            view("{{ route('cetakInvoice') }}/{{ $order->order_id }}?type="+type)
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
