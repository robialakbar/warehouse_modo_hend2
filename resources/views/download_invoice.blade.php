<!--
 Developer      : Desman Harianto Pardosi
 WhatsApp       : 0811 666 824
 E-mail         : desman@pardosi.net
-->
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
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <style type="text/css">
  body {font-family:"Source Sans Pro",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";background-color :#fff;font-size:10px;}
  .header, .footer {width:100%;padding:0px;text-align:center;}
  .header, .footer span {display: block;}
  .space {padding-top: 10px;}
  .line{border-bottom:1px dotted #000;padding:5px;}
  h1 {font-size: 10px;font-weight: 900;margin: 0px;text-transform:uppercase;}
  h2 {font-size: 8px;font-weight: 700;margin: 0px;}
  h3 {font-size: 6px;margin: 0px;}
  .title {margin-top:5px;margin-bottom:0px;font-size:10px;font-weight:900;text-decoration:underline;}
  small {font-size:5px;}
  table {
    width: 100%;
  }
  .text-left{text-align: left;}
  .text-center{text-align: center;}
  .text-right{text-align: right;}
  .left{float:left;}
  .right{float:right;}
  .padding{padding:5px;}
  .bold{font-weight:bold;}
  .logo{width:150px;}
  span {display:block !important;}
  </style>
  <title>Cetak Struk Order ID #{{ $data["order_id"] }}</title>
</head>
<body>
  <div class="header">
    @if(getSetting("invoice_show_logo") == "true")<p><img class="logo"src="{{ asset("/img/logo2.png") }}" /></p>@endif
    <span>No. HP/Telp.: {{ !empty($data["warehouse_nohp"]) ? $data["warehouse_nohp"]:"" }}</span>
    <span>Alamat: {{ !empty($data["warehouse_address"]) ? $data["warehouse_address"]:"" }}</span>
  </div>
  <div class="line"></div>
  <div>
    <table>
      <tr>
        <td>Customer : {{ Str::limit($data["customer_name"], 20, "") }}<td>
        <td>No. HP : {{ $data["customer_nohp"] }}</td>
      </tr>
      <tr>
        <td>No. Invoice : {{ $data["no_invoice"] }}<td>
        <td>Tgl. : {{ $data["tgl_invoice"] }}</td>
      </tr>
      <tr>
        <td>Nama {{ ($data["order_type"] == 0)? "Sales":"Mekanik" }} : {{ $data["nama_mekanik"] }}<td>
        <td>Catatan : {{ $data["catatan"] }}<td>
      </tr>
    </table>
  </div>
  <div class="line"></div>
  <table>
    <thead>
      <tr>
        <th class="text-left">Item</th>
        <th>Qty</th>
        <th class="text-right">Total</th>
      </tr>
    </thead>
    <tbody>
      @if(count($data["items"]) > 0)
        @foreach($data["items"] as $d)
          @if(!empty($d->product_name))
          <tr>
            <td>{{ Str::limit($d->product_name, 20, "") }}</td>
            <td class="text-center">{{ $d->amount }}</td>
            <td class="text-right">{{ number_format($d->sale_price*$d->amount, 0, ',', '.') }}</td>
          </tr>
          @endif
        @endforeach
      @endif
    </tbody>
  </table>
  <div class="line"></div>
  <table class="bold">
    <tbody>
      <tr>
        <td class="text-right">Diskon</td>
        <td class="text-right">{{ number_format($data["diskon"], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="text-right">Total</td>
        <td class="text-right">{{ number_format($data["total_harga"], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="text-right">Bayar ({{ ($data['metode_pembayaran'] == 0) ? "Tunai" : (($data['metode_pembayaran'] == 1) ? "QRIS" : (($data['metode_pembayaran'] == 2) ? "Transfer Bank" : "EDC")) }})</td>
        <td class="text-right">{{ number_format($data["pembayaran"], 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="text-right">Kembalian</td>
        <td class="text-right">{{ number_format($data["kembalian"], 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
  <div class="space"></div>
  <div class="space"></div>
  <div class="header">
    <h3>{{ getSetting("invoice_footer") }}</h3>
  </div>
</body>
</html>