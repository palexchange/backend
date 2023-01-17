<html>

<head>
    <style>
        body {
        
            /* font-family: 'DejaVuSansMono'  ; */
            font-family: 'XBRiyaz'  ;
            /* font-family: 'Lateef'  ; */
            /* font-family: 'Sun-ExtA Sun-ExtB'  ; */
            /* font-family: 'KFGQPC Uthman Taha'  ; */
            /* font-family: 'Lateef'  ; */
            font-size: 11pt;
        }

        p {
            margin: 0pt;
        }

        table.items {
            border: 0.1mm solid #000000;
        }

        td {
            text-align: right;
            vertical-align: top;
        }


        .items td {
            border-left: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }

        table thead th {
            background-color: #EEEEEE;
            text-align: right;
            border: 0.1mm solid #000000;
            font-variant: small-caps;
        }

        .items td.blanktotal {
            background-color: #EEEEEE;
            border: 0.1mm solid #000000;
            background-color: #FFFFFF;
            border: 0mm none #000000;
            border-top: 0.1mm solid #000000;
            border-right: 0.1mm solid #000000;
        }

        .items td.totals {
            text-align: right;
            border: 0.1mm solid #000000;
        }

        .items td.cost {
            text-align: "."center;
        }
    </style>
</head>

<body>

    <htmlpageheader name="myheader">
        <table width="100%">
            <tr>

                {{-- <td width="50%" style="text-align: left;">Invoice No.<br /><span
                        style="font-weight: bold; font-size: 12pt;">0012345</span></td> --}}

                <td width="40%" style="text-align: left; "><span style="font-weight: bold; font-size: 14pt;">PalExchange
                        Co.</span><br />Omar Almokhtar<br />Gaza<br />Beside Palestine Bank</td>

                <td width="20%" style="text-align: left; ">
                        <h2>
                        <img width="60" height="60" src="data:image/jpeg;base64,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"
                            alt="Red dot" />
                       <br>
                        {{ $title }}
                    </h2>
                </td>
                <td width="40%" style="text-align: right; "><span style="font-weight: bold; font-size: 14pt;">الفلسطينية
                        للصرافة
                    </span><br />شارع عمر المختار<br />غزة<br />بجوار الجرجازي للعصائر<br />
                    <span>&#9742;</span> 059 21 58845 15
                </td>

            </tr>
        </table>
    </htmlpageheader>
    <htmlpagefooter name="myfooter">
        <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
            Page {PAGENO} of {nb}
        </div>
    </htmlpagefooter>
    <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
    <sethtmlpagefooter name="myfooter" value="on" />

    @php
        use Carbon\Carbon;
    @endphp
    <div style="text-align: left">Date: {{ Carbon::now()->timezone('Asia/Gaza')->toDateTimeString() }}</div>
    <table width="100%" cellpadding="10">
        <tr>
            <td width="45%" style="border: 0.1mm solid #888888; "><span style=" color: #555555;  ">SOLD
                    TO:</span><br /><br />345
                Anotherstreet<br />Little Village<br />Their City<br />CB22 6SO</td>
            <td width="10%">&nbsp;</td>
            <td width="45%" style="border: 0.1mm solid #888888;"><span style=" color: #555555;  ">SHIP
                    TO:</span><br /><br />345
                Anotherstreet<br />Little Village<br />Their City<br />CB22 6SO</td>
        </tr>
    </table>
    <br />
    <table class="items" width="100%" style=" border-collapse: collapse; " cellpadding="8">
        <thead>
            <tr>
                @foreach ($headers as $head)
                    <th width="15%">{{ $head }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <!-- ITEMS HERE -->

            @foreach ($items as $item)
                <tr>
                    @foreach ($headers as $head)
                        <td width="15%">{{ $item[$head] }}</td>
                    @endforeach
                </tr>
            @endforeach
            {{-- <tr>
                <td class="blanktotal" colspan="2" rowspan="6"></td>
                <td class="totals">Subtotal:</td>
                <td class="totals cost">&pound;1825.60</td>
            </tr>
            <tr>
                <td class="totals">Tax:</td>
                <td class="totals cost">&pound;18.25</td>
            </tr>
            <tr>
                <td class="totals">Shipping:</td>
                <td class="totals cost">&pound;42.56</td>
            </tr>
            <tr>
                <td class="totals"><b>TOTAL:</b></td>
                <td class="totals cost"><b>&pound;1882.56</b></td>
            </tr>
            <tr>
                <td class="totals">Deposit:</td>
                <td class="totals cost">&pound;100.00</td>
            </tr>
            <tr>
                <td class="totals"><b>Balance due:</b></td>
                <td class="totals cost"><b>&pound;1782.56</b></td>
            </tr> --}}
            <!-- END ITEMS HERE -->

        </tbody>
    </table>

</body>

</html>
