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
  h1 {font-size: 12px;font-weight: 900;margin: 0px;text-transform:uppercase;}
  h2 {font-size: 10px;font-weight: 700;margin: 0px;}
  h3 {font-size: 8px;margin: 0px;}
  .title {margin-top:5px;margin-bottom:0px;font-size:12px;font-weight:900;text-decoration:underline;}
  small {font-size:8px;}
  table {
    width: 100%;
  }
  table,th,tr,td {border:1px solid #000;border-collapse:collapse;}
  td {padding: 2px;}
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
  <title>Laporan</title>
</head>
<body>
  <table>
    <thead>
    @foreach ( $heading as $h )
      <th>{{ $h }}</th>
    @endforeach
    </thead>
    <tbody>
      @foreach ( $LaporanExport as $l )
        <tr>
          <td class="text-center">{{ $l["NO."] }}</td>
          <td class="text-center">{{ $l["ORDER ID"] }}</td>
          <td class="text-center">{{ $l["NO. INVOICE"] }}</td>
          <td class="text-center">{{ $l["TGL. INVOICE"] }}</td>
          <td class="text-center">{{ $l["JENIS ORDER"] }}</td>
          <td>{{ $l["NAMA CUSTOMER"] }}</td>
          <td>{{ $l["NO. HP"] }}</td>
          <td class="text-center">{{ $l["NO. POLISI"] }}</td>
          <td class="text-center">{{ $l["JENIS KENDARAAN"] }}</td>
          <td class="text-center">{{ $l["KM KENDARAAN"] }}</td>
          <td class="text-center">{{ $l["TAHUN KENDARAAN"] }}</td>
          <td class="text-center">{{ $l["METODE PEMBAYARAN"] }}</td>
          <td class="text-right">{{ number_format($l["TOTAL PEMBELIAN"], 0, ",", ".") }}</td>
          <td class="text-right">{{ number_format($l["TOTAL HARGA JUAL"], 0, ",", ".") }}</td>
          <td class="text-right">{{ number_format($l["TOTAL KEUNTUNGAN"], 0, ",", ".") }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>