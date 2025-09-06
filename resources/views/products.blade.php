@extends('layouts.main')
@section('title', __('Products'))
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
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fa fa-money"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Total Modal</span>
                        <span class="info-box-number">Rp {{ number_format($total_modal, 0 , ",", ".") }} ({{ $total_all_stock }})</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-product" onclick="addProduct()"><i class="fa fa-plus"></i> Add New Product</button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#import-product" onclick="importProduct()"><i class="fa fa-file-excel"></i> Import Product (XLS)</button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#form-download" onclick="download()"><i class="fa fa-download"></i> Download (XLS/PDF)</button>
                <div class="card-tools">
                    <form>
                        <div class="input-group input-group">
                            <input type="text" class="form-control" name="q" placeholder="Search">
                            <input type="hidden" name="category" value="{{ Request::get('category') }}">
                            <input type="hidden" name="sort" value="{{ Request::get('sort') }}">
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
                <div class="form-group row col-sm-12">
                    <div class="col-sm-6">
                        <label for="sort" class="col-sm-3 col-form-label">Sort</label>
                        <div class="col-sm-9">
                            <form id="sorting" action="" method="get">
                                <input type="hidden" name="q" value="{{ Request::get('q') }}">
                                <input type="hidden" name="category" value="{{ Request::get('category') }}">
                                <select class="form-control select2" style="width: 100%;" id="sort" name="sort">
                                    <option value="" {{ Request::get('sort') == null? 'selected':'' }}>-</option>
                                    <option value="name_az" {{ Request::get('sort') == 'name_az'? 'selected':'' }}>Nama Produk (A-Z)</option>
                                    <option value="name_za" {{ Request::get('sort') == 'name_za'? 'selected':'' }}>Nama Produk (Z-A)</option>
                                    <option value="category_az" {{ Request::get('sort') == 'category_az'? 'selected':'' }}>Kategori (A-Z)</option>
                                    <option value="category_za" {{ Request::get('sort') == 'category_za'? 'selected':'' }}>Kategori (Z-A)</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="sort" class="col-sm-3 col-form-label">Kategori</label>
                        <div class="col-sm-9">
                            <form id="filtering" action="" method="get">
                                <input type="hidden" name="q" value="{{ Request::get('q') }}">
                                <input type="hidden" name="category" value="{{ Request::get('category') }}">
                                <select class="form-control select2" style="width: 100%;" id="category2" name="category">
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No.</th>
                                <th>{{ __('Kode Produk') }}</th>
                                <th>{{ __('Nama Produk') }}</th>
                                <th>{{ __('Kategori') }}</th>
                                <th>{{ __('Jumlah') }}</th>
                                <th>{{ __('Harga Pembelian (Rp)') }}</th>
                                <th>{{ __('Harga Jual (Rp)') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(count($products) > 0)
                            @foreach($products as $key => $d)
                            @php
                                $data = [
                                            "no"        => $products->firstItem() + $key,
                                            "pid"       => $d->product_id,
                                            "pcode"     => $d->product_code,
                                            "pname"     => $d->product_name,
                                            "cname"     => $d->category_name,
                                            "cval"      => $d->category_id,
                                            "pamount"   => $d->product_amount,
                                            "pprice"    => $d->purchase_price,
                                            "sprice"    => $d->sale_price
                                        ];
                            @endphp
                            <tr>
                                <td class="text-center">{{ $data['no'] }}</td>
                                <td class="text-center">{{ $data['pcode'] }}</td>
                                <td>{{ $data['pname'] }}</td>
                                <td>{{ $data['cname'] }}</td>
                                <td class="text-center"><span class="{{ ($data['pamount'] <= 10)? 'badge bg-warning':'' }}">{{ $data['pamount'] }}</span></td>
                                <td class="text-center">{{ number_format($data['pprice'], 0, ",", ".") }}</td>
                                <td class="text-center">{{ $data['sprice'] ? number_format($data['sprice'], 0, ",", "."):"-" }}</td>
                                <td class="text-center"><button title="Edit Produk" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-product" onclick="editProduct({{ json_encode($data) }})"><i class="fa fa-edit"></i></button> <button title="Lihat Barcode" type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#lihat-barcode" onclick="barcode('{{ $d->product_code }}')"><i class="fa fa-barcode"></i></button> @if(Auth::user()->role == 0)<button title="Hapus Produk" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-product" onclick="deleteProduct({{ json_encode($data) }})"><i class="fa fa-trash"></i></button>@endif</td>
                            </tr>
                            @endforeach
                        @else
                            <tr class="text-center">
                                <td colspan="8">{{ __('No data.') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
        {{ $products->appends(request()->except('page'))->links("pagination::bootstrap-4") }}
        </div>
    </div>
    <div class="modal fade" id="add-product">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Add New Product') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="save" action="{{ route('products.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="save_id" name="id">
                        <div class="form-group row">
                            <label for="product_code" class="col-sm-4 col-form-label">{{ __('Product Code') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="product_code" name="product_code">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="product_name" class="col-sm-4 col-form-label">{{ __('Product Name') }}</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="product_name" name="product_name">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="purchase_price" class="col-sm-4 col-form-label">{{ __('Purchase Price') }} (Rp)</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="purchase_price" name="purchase_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="sale_price" class="col-sm-4 col-form-label">{{ __('Sale Price') }} (Rp)</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="sale_price" name="sale_price">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="category" class="col-sm-4 col-form-label">Category</label>
                            <div class="col-sm-8">
                                <select class="form-control select2" style="width: 100%;" id="category" name="category">
                                </select>
                            </div>
                        </div>
                        <div id="barcode_preview_container" class="form-group row">
                            <label class="col-sm-4 col-form-label">Barcode</label>
                            <div class="col-sm-8">
                                <img id="barcode_preview"/>
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
    <div class="modal fade" id="lihat-barcode">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Barcode') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <input type="hidden" id="pcode_print">
                        <img id="barcode"/>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" id="jumlah_barcode" name="jumlah_barcode" min="1" placeholder="Jumlah Label">
                        <div class="input-group-append">
                            <button class="btn btn-primary" onclick="printBarcode()">Print Barcode</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Tutup') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete-product">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">{{ __('Delete Product') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('products.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="id">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus product code <span id="pcode" class="font-weight-bold"></span>?</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                    <button id="button-save" type="button" class="btn btn-danger" onclick="document.getElementById('delete').submit();">{{ __('Ya, hapus') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="import-product">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title">Import Product (Excel)</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <form role="form" enctype="multipart/form-data" id="import" action="{{ route('products.import') }}" method="post">
                        @csrf
                        <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file" name="file">
                            <label class="custom-file-label" for="file">Choose file</label>
                        </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                <button type="button" class="btn btn-default" id="download-template">{{ __('Download Template') }}</button>
                <button type="button" class="btn btn-primary" onclick="$('#import').submit();">{{ __('Import') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="form-download">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title">Download</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <form role="form" enctype="multipart/form-data" id="download" action="{{ route('products') }}" method="get">
                        <input type="hidden" id="q" name="q" value="{{ Request::get('q') }}">
                        <input type="hidden" id="category" name="category" value="{{ Request::get('category') }}">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="harga_beli" name="harga_beli">
                                            <label class="form-check-label">Harga Pembelian (Rp)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="harga_jual" name="harga_jual">
                                            <label class="form-check-label">Harga Jual (Rp)</label>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                            <div class="form-group row">
                                <label for="format" class="col-sm-3 col-form-label">Format</label>
                                <div class="col-sm-9">
                                    <select id="format" name="format">
                                        <option value="xls">XLS</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                <button type="button" class="btn btn-primary" onclick="$('#download').submit();">{{ __('Download') }}</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('custom-js')
    <script src="/plugins/toastr/toastr.min.js"></script>
    <script src="/plugins/select2/js/select2.full.min.js"></script>
    <script src="/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <script>
        $(function () {
            bsCustomFileInput.init();
            var user_id;
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            $('#product_code').on('change', function() {
                var code = $('#product_code').val();
                if(code != null && code != ""){
                    $("#barcode_preview").attr("src", "/products/barcode/"+code);
                    $('#barcode_preview_container').show();
                }
            });
            getCategory({{Request::get('category')}});
        });

        $('#sort').on('change', function() {
            $("#sorting").submit();
        });

        $('#category2').on('change', function() {
            $("#filtering").submit();
        });

        function getCategory(val){
            $.ajax({
                url: '/products/categories',
                type: "GET",
                data: {"format": "json"},
                dataType: "json",
                success:function(data) {
                    $('#category').empty();
                    $('#category2').empty();
                    $('#category').append('<option value="">.:: Pilih Kategori ::.</option>');
                    $('#category2').append('<option value="">.:: Pilih Kategori ::.</option>');
                    $.each(data, function(key, value) {
                        if(value.category_id == val){
                            $('#category').append('<option value="'+ value.category_id +'" selected>'+ value.category_name +'</option>');
                            $('#category2').append('<option value="'+ value.category_id +'" selected>'+ value.category_name +'</option>');
                        } else {
                            $('#category').append('<option value="'+ value.category_id +'">'+ value.category_name +'</option>');
                            $('#category2').append('<option value="'+ value.category_id +'">'+ value.category_name +'</option>');
                        }
                    });
                }
            });
        }

        function resetForm(){
            $('#save').trigger("reset");
            $('#barcode_preview_container').hide();
        }

        function addProduct(){
            $('#modal-title').text("Add New Product");
            $('#button-save').text("Tambahkan");
            resetForm();
            getCategory();
        }

        function editProduct(data){
            $('#modal-title').text("Edit Product");
            $('#button-save').text("Simpan");
            resetForm();
            $('#save_id').val(data.pid);
            $('#product_code').val(data.pcode);
            $('#product_name').val(data.pname);
            $('#purchase_price').val(data.pprice);
            $('#sale_price').val(data.sprice);
            getCategory(data.cval);
            $('#product_code').change();
        }

        function barcode(code){
            $("#pcode_print").val(code);
            $("#barcode").attr("src", "/products/barcode/"+code);
        }

        function printBarcode(){
            var code    = $("#pcode_print").val();
            var jumlah  = $('#jumlah_barcode').val();
            var url     = "/products/barcode/"+code+"?print=true&jumlah="+jumlah;
            window.open(url,'window_print','menubar=0,resizable=0');
        }

        function deleteProduct(data){
            $('#delete_id').val(data.pid);
            $('#pcode').text(data.pcode);
        }

        $("#download-template").click(function(){
            $.ajax({
                url: '/downloads/template_import_product.xls',
                type: "GET",
                xhrFields: {
                    responseType: 'blob'
                },
                success:function(data) {                    
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(data);
                    a.href = url;
                    a.download = "template_import_product.xls";
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                }
            });
        });
        
        function download(type){
            //window.location.href="{{ route('products') }}?search={{ Request::get('search') }}&category={{ Request::get('category') }}&dl="+type;
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