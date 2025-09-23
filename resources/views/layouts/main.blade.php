<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/plugins/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="/css/adminlte.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
	<link rel="stylesheet" href="{{ url('/plugins/select2/css/select2.min.css') }}" />
  @hasSection('custom-css')
    @yield('custom-css')
  @endif
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link">@yield('title')</span>
      </li>
    </ul>
    @if(!empty($warehouse))
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
          @if(Session::has('selected_warehouse_name'))
          <i class="fa fa-warehouse"></i>
          <span>{{ Session::get('selected_warehouse_name') }}</span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
          <span class="dropdown-item dropdown-header">Warehouse</span>
          @foreach($warehouse as $w)
            <a href="{{ route('warehouse') }}/change/{{ $w->warehouse_id }}" class="dropdown-item">
              {{ $w->warehouse_name }}
            </a>
          @endforeach
        </div>
      </li>
    </ul>
    @endif
  </nav>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="/" class="brand-link">
      <img src="{{ asset('/img/logo.png') }}" alt="AdminLTE Logo" class="brand-image elevation-3" style="opacity: .8"/>
      <span class="brand-text font-weight-bold">{{ config('app.name', 'Warehouse') }}</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            @if(Auth::check())
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'home')? 'active':''}}" href="{{ route('home') }}">
                    <i class="nav-icon fa fa-home"></i>
                    <p class="text">{{ __('Dashboard') }}</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'products.wip')? 'active':''}}" href="{{ route('products.wip') }}">
                    <i class="nav-icon fa fa-spinner"></i>
                    <p class="text">{{ __('Work In Progress (WIP)') }}</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'products.wip.history')? 'active':''}}" href="{{ route('products.wip.history') }}">
                    <i class="nav-icon fa fa-history"></i>
                    <p class="text">{{ __('WIP History') }}</p>
                </a>
            </li>
            <li class="nav-header">Kasir</li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'order')? 'active':''}}" href="{{ route('order') }}">
                    <i class="nav-icon fa fa-cart-plus"></i>
                    <p class="text">Order</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'order.list')? 'active':''}}" href="{{ route('order.list') }}">
                    <i class="nav-icon fa fa-list-ol"></i>
                    <p class="text">Order List</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'laporan')? 'active':''}}" href="{{ route('laporan') }}">
                    <i class="nav-icon fa fa-line-chart"></i>
                    <p class="text">Laporan</p>
                </a>
            </li>
            <li class="nav-header">Master</li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'products')? 'active':''}}" href="{{ route('products') }}">
                    <i class="nav-icon fa fa-cubes"></i>
                    <p class="text">Products</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'services')? 'active':''}}" href="{{ route('services') }}">
                    <i class="nav-icon fa fa-wrench"></i>
                    <p class="text">Jasa Servis</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'products.categories')? 'active':''}}" href="{{ route('products.categories') }}">
                    <i class="nav-icon fa fa-sort-alpha-asc"></i>
                    <p class="text">Categories</p>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'orders.sync')? 'active':''}}" href="{{ route('orders.sync') }}">
                    <i class="nav-icon fa fa-refresh"></i>
                    <p class="text">Refresh SYNC ORDER</p>
                </a>
            </li>
            @if(Auth::user()->role == 0)
            <li class="nav-item">
                <a class="nav-link {{ (Route::current()->getName() == 'city')? 'active':''}}" href="{{ route('city') }}">
                    <i class="nav-icon fa fa-map-marker"></i>
                    <p class="text">Kota</p>
                </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ (Route::current()->getName() == 'warehouse')? 'active':''}}" href="{{ route('warehouse') }}">
                  <i class="nav-icon fa fa-building"></i>
                  <p class="text">{{ __('Warehouse') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ (Route::current()->getName() == 'users')? 'active':''}}" href="{{ route('users') }}">
                  <i class="nav-icon fa fa-users"></i>
                  <p class="text">{{ __('Users') }}</p>
              </a>
            </li>
            @endif
            <li class="nav-header">Settings</li>
            @if(Auth::user()->role == 0)
            <li class="nav-item">
              <a class="nav-link {{ (Route::current()->getName() == 'settings')? 'active':''}}" href="{{ route('settings') }}">
                  <i class="nav-icon fa fa-cogs"></i>
                  <p class="text">Umum</p>
              </a>
            </li>
            @endif
            <li class="nav-item">
              <a class="nav-link {{ (Route::current()->getName() == 'myaccount')? 'active':''}}" href="{{ route('myaccount') }}">
                  <i class="nav-icon fa fa-user"></i>
                  <p class="text">{{ __('My Account') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <form id="logout" action="{{ route('logout') }}" method="post">@csrf</form>
              <a class="nav-link" href="javascript:;" onclick="document.getElementById('logout').submit();">
                  <i class="nav-icon fa fa-sign-out text-danger"></i>
                  <p class="text">{{ __('Logout') }} ({{ Auth::user()->username }})</p>
              </a>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="nav-icon fa fa-sign-out-alt text-danger"></i>
                    <p class="text">{{ __('Login') }}</p>
                </a>
            </li>
            @endif
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    @yield('content')
  </div>

  <footer class="main-footer">
    <b>Version</b> {{ config('app.version') }}
  </footer>

  <aside class="control-sidebar control-sidebar-dark">
  </aside>
</div>

<script src="/plugins/jquery/jquery.min.js"></script>
<script src="/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/js/adminlte.js"></script>
<script src="{{ url('/plugins/select2/js/select2.min.js') }}"></script>
@hasSection('custom-js')
    @yield('custom-js')
@endif
<script>
$('.select2').select2({width:"100%"});
function view(url, target=null){
  if(target == null){
    target = "_blank";
  }
  window.open(url, target);
}
</script>
</body>
</html>
