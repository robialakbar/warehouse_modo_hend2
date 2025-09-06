@php
function getSetting($name){
	$result		= "";
	$settings	= DB::table("settings")->select("setting_value")->where("setting_name", $name)->first();
	if($settings){
		$result = $settings->setting_value;
	}

	return $result;
}
@endphp
@extends('layouts.main')
@section('title', __('Settings'))
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
        <div class="row">
            <div class="col-md-5 mx-auto">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Settings</h3>
                    </div>
                    <div class="card-body">
                        <form role="form" id="update" action="{{ route('settings.update') }}" method="post">
                            @csrf
                            <div class="form-group row">
                                <label for="invoice_show_logo" class="col-sm-4 col-form-label">Tampilkan Logo Invoice</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" style="width: 100%;" id="invoice_show_logo" name="invoice_show_logo">
                                        <option value="true" {{ (getSetting("invoice_show_logo") == "true")? "selected":"" }}>Ya</option>
                                        <option value="false" {{ (getSetting("invoice_show_logo") == "false")? "selected":"" }}>Tidak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="invoice_footer" class="col-sm-4 col-form-label">Invoice Footer</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoice_footer" name="invoice_footer" value="{{ getSetting('invoice_footer') }}">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button id="button-update" type="button" class="btn btn-primary float-right" onclick="$('#update').submit();">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('custom-js')
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