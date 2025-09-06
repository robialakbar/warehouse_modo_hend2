<!--
 Developer      : Desman Harianto Pardosi
 WhatsApp       : 0811 666 824
 E-mail         : desman@pardosi.net
-->
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
  <title>Daftar Produk</title>
</head>
<body>
  <table>
    <thead>
    @foreach ( $heading as $h )
      <th>{{ $h }}</th>
    @endforeach
    </thead>
    <tbody>
      @foreach ( $productExport as $l )
        <tr>
          <td class="text-center">{{ $l["NO."] }}</td>
          <td class="text-center">{{ $l["KODE PRODUK"] }}</td>
          <td class="text-left">{{ $l["NAMA PRODUK"] }}</td>
          <td class="text-left">{{ $l["KATEGORI"] }}</td>
          <td class="text-right">{{ $l["JUMLAH"] }}</td>
          @if(!empty($l["HARGA BELI (RP)"]))
          <td class="text-right">{{ number_format($l["HARGA BELI (RP)"], 0, ",", ".") }}</td>
          @endif
          @if(!empty($l["HARGA JUAL (RP)"]))
          <td class="text-right">{{ number_format($l["HARGA JUAL (RP)"], 0, ",", ".") }}</td>
          @endif
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>