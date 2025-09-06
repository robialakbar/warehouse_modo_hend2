@extends('layouts.main')
@section('title', __('Jasa Servis'))
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
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-product" onclick="addProduct()"><i class="fa fa-plus"></i> Tambah Jasa Servis</button>
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
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No.</th>
                                <th>Nama Jasa Servis</th>
                                <th>Biaya (Rp)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(count($services) > 0)
                            @foreach($services as $key => $d)
                            @php
                                $data = [
                                            "no"        => $services->firstItem() + $key,
                                            "service_id"    => $d->service_id,
                                            "nama_jasa"     => $d->nama_jasa,
                                            "biaya"         => $d->biaya
                                        ];
                            @endphp
                            <tr>
                                <td class="text-center">{{ $data['no'] }}</td>
                                <td>{{ $data['nama_jasa'] }}</td>
                                <td class="text-center">{{ number_format($data['biaya'], 0, ",", ".") }}</td>
                                <td class="text-center"><button title="Edit Jasa" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-product" onclick="editProduct({{ json_encode($data) }})"><i class="fa fa-edit"></i></button> @if(Auth::user()->role == 0)<button title="Hapus Jasa" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-product" onclick="deleteProduct({{ json_encode($data) }})"><i class="fa fa-trash"></i></button>@endif</td>
                            </tr>
                            @endforeach
                        @else
                            <tr class="text-center">
                                <td colspan="4">{{ __('No data.') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
        {{ $services->appends(request()->except('page'))->links("pagination::bootstrap-4") }}
        </div>
    </div>
    <div class="modal fade" id="add-product">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Tambah Jasa Servis</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="save" action="{{ route('services.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="save_id" name="id">
                        <div class="form-group row">
                            <label for="nama_jasa" class="col-sm-4 col-form-label">Nama Jasa Servis</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="nama_jasa" name="nama_jasa">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="biaya" class="col-sm-4 col-form-label">Biaya (Rp)</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="biaya" name="biaya">
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
    <div class="modal fade" id="delete-product">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Hapus Jasa Servis</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('services.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="id">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus jasa servis <span id="delete_name" class="font-weight-bold"></span>?</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                    <button id="button-save" type="button" class="btn btn-danger" onclick="document.getElementById('delete').submit();">{{ __('Ya, hapus') }}</button>
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
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });

        function resetForm(){
            $('#save').trigger("reset");
        }

        function addProduct(){
            $('#modal-title').text("Tambah Jasa Servis");
            $('#button-save').text("Tambahkan");
            resetForm();
        }

        function editProduct(data){
            $('#modal-title').text("Edit Jasa Servis");
            $('#button-save').text("Simpan");
            resetForm();
            $('#save_id').val(data.service_id);
            $('#nama_jasa').val(data.nama_jasa);
            $('#biaya').val(data.biaya);
        }

        function deleteProduct(data){
            $('#delete_id').val(data.service_id);
            $('#delete_name').text(data.nama_jasa);
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