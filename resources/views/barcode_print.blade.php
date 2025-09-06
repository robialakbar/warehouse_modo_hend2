<!DOCTYPE html>
<head>
    <title>Print Barcode</title>
    <style>
        table {
            width: auto;
            border-spacing: 10mm;
        }
        th, td {
            border: none;
            width: 50mm;
            height: 20mm;
            padding: 0;
            box-sizing: border-box;
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body onload="window.print()" style="text-align:center;">
    <table>
        <tr>
        @for($i=0;$i<=$jumlah-1;$i++)
                <td><img width="300px" src="data:image/png;base64,{{ $barcode }}" /></td>
            @if($i%3==2)
                </tr>
            @endif
        @endfor
    </table>
</body>
</html>