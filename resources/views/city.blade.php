@extends('layouts.main')
@section('title', __('Kota'))
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
                @if(Auth::user()->role == 0)
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-city" onclick="addCity()"><i class="fa fa-plus"></i> Tambah Kota</button>
                @endif
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
                        <th>No.</th>
                        <th>Nama Kota</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @if(count($city) > 0)
                    @foreach($city as $key => $d)
                    @php
                        $data = ["city_id" => $d->city_id, "city_name" => $d->city_name];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $city->firstItem() + $key }}</td>
                        <td>{{ $data['city_name'] }}</td>
                        <td class="text-center">@if(Auth::user()->role == 0)<button title="Edit Kota" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#add-city" onclick="editCity({{ json_encode($data) }})"><i class="fa fa-edit"></i></button> <button title="Hapus Kota" type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#delete-city" onclick="deleteCity({{ json_encode($data) }})"><i class="fa fa-trash"></i></button>@endif</td>
                    </tr>
                    @endforeach
                @else
                    <tr class="text-center">
                        <td colspan="3">{{ __('No data.') }}</td>
                    </tr>
                @endif
                </tbody>
                </table>
            </div>
        </div>
        <div>
        {{ $city->links("pagination::bootstrap-4") }}
        </div>
    </div>
    @if(Auth::user()->role == 0)
    <div class="modal fade" id="add-city">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Tambah Kota</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="save" action="{{ route('city.save') }}" method="post">
                        @csrf
                        <input type="hidden" id="city_id" name="city_id">
                        <div class="form-group row">
                            <label for="city_name" class="col-sm-4 col-form-label">Nama Kota</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="city_name" name="city_name">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button id="button-save" type="button" class="btn btn-primary" onclick="$('#save').submit();">Tambah</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete-city">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modal-title" class="modal-title">Hapus Kota</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" id="delete" action="{{ route('city.delete') }}" method="post">
                        @csrf
                        @method('delete')
                        <input type="hidden" id="delete_id" name="delete_id">
                    </form>
                    <div>
                        <p>Anda yakin ingin menghapus kota <span id="delete_name" class="font-weight-bold"></span>?</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Batal') }}</button>
                    <button id="button-save" type="button" class="btn btn-danger" onclick="$('#delete').submit();">{{ __('Ya, hapus') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</section>
@endsection
@section('custom-js')
    <script>
        function resetForm(){
            $('#save').trigger("reset");
            $('#city_id').val('');
        }

        function addCity(){
            resetForm();
            $('#modal-title').text("Tambah Kota");
            $('#button-save').text("Tambah");
        }

        function editCity(data){
            resetForm();
            $('#modal-title').text("Edit City");
            $('#button-save').text("Simpan");
            $('#city_id').val(data.city_id);
            $('#city_name').val(data.city_name);
        }

        function deleteCity(data){
            $('#delete_id').val(data.city_id);
            $('#delete_name').text(data.city_name);
        }
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