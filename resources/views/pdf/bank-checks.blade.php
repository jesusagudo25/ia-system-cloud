<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bank Checks {{ $preview ? 'Preview' : '' }}</title>
    <style>
        @font-face {
            font-family: 'micr-e13b';
            src: url({{ storage_path('fonts/micr-e13b.ttf') }}) format('truetype');
        }

        @font-face {
            font-family: 'Calibri-Bold';
            src: url({{ storage_path('fonts/Calibri-Bold.ttf') }}) format('truetype');
        }


        @font-face {
            font-family: 'Calibri-Regular';
            src: url({{ storage_path('fonts/Calibri-Regular.ttf') }}) format('truetype');
        }

        @page {
            margin: 13px;
        }

        body {
            margin: 13px;
            font-family: 'Calibri-Regular'
        }

        .font-body {
            font-family: 'Calibri-Regular'
        }

        /* Style Definitions */
        p.MsoNormal,
        li.MsoNormal,
        div.MsoNormal {
            margin-top: 0cm;
            margin-right: 0cm;
            margin-bottom: 10.0pt;
            margin-left: 0cm;
            line-height: 85%;
            font-size: 11.0pt;
            font-family: 'Calibri-Regular', sans-serif;
        }

        td,
        tr,
        th {
            font-family: 'Calibri-Regular', sans-serif;
        }

        .msoFalse {
            margin-top: 0cm;
            margin-right: 0cm;
            margin-bottom: 10.0pt;
            margin-left: 0cm;
            line-height: 85%;
            font-size: 0.5pt;
        }

        p.MsoBold {
            margin-top: 0cm;
            margin-right: 0cm;
            margin-bottom: 10.0pt;
            margin-left: 0cm;
            line-height: 85%;
            font-size: 11.0pt;
            font-family: 'Calibri-Bold', sans-serif;
        }

        .MsoPapDefault {
            margin-bottom: 10.0pt;
            line-height: 115%;
        }

        @page WordSection1 {
            size: 612.0pt 792.0pt;
            margin: 8.5pt 17.85pt 14.45pt 18.15pt;
        }

        div.WordSection1 {
            page: WordSection1;
        }

        .check-number {
            font-family: 'micr-e13b';
        }

        .id-number {
            font-family: 'Calibri-Bold';
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body lang=ES-PA style='word-wrap:break-word'>


    @if (count($checks) > 0)

        @foreach ($checks as $check)
            <div class=WordSection1>

                <table class=MsoNormalTable cellspacing=0 cellpadding=0 width="100%"
                    style='width:100.0%;border-collapse:collapse;border:none; margin-bottom: 5.5px;' border="0">
                    <tr style=''>
                        <td width="65%" nowrap colspan=8 rowspan=2 valign=top
                            style='width:65.22%; padding:0cm 5.4pt 0cm 5.4pt;'>
                            <p class=MsoBold style='margin-bottom:3;'><span lang=EN-US
                                    style='color:black; font-size: 14.5px'><strong>INTERPRETERS ACADEMY</strong></span>
                            </p>
                            <p class=MsoBold style='margin-bottom:3;'><span lang=EN-US
                                    style='color:black; font-size: 14.5px;'><strong>OF JACKSONVILLE LLC</strong></span>
                            </p>
                            <p class=MsoNormal style='margin-bottom:3;'><span lang=EN-US
                                    style='font-size:10.0pt;color:black; font-size: 13.5px;'><strong>2280 SHEPARD ST APT
                                        404</strong></span></p>
                            <p class=MsoNormal style='margin-bottom:3;'><span lang=EN-US
                                    style='font-size:10.0pt;color:black; font-size: 13.5px;'><strong>JACKSONVILLE, FL
                                        32211-3284</strong></span></p>
                        </td>
                        <td nowrap colspan=5 rowspan=2 valign=top align=right <img src={{ asset('images/vystar.jpg') }}
                            alt="FABLAB" style="width: 70%;"> </td>
                        </td>
                        <td width="16%" nowrap colspan=3 valign=bottom
                            style='width:16.82%; border-left:none;padding:0cm 5.4pt 0cm 5.4pt; '>
                            <p class='MsoNormal'>&nbsp; </p>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                            <p class='MsoNormal'>&nbsp; </p>
                        </td>
                        <td width="16%" nowrap colspan=3 valign=center
                            style=' border-left:none;padding:0cm 5.4pt 0cm 5.4pt; text-align: center;'>
                            <strong><span lang=EN-US style='font-size:14.0pt;color:black'>{{ $check->id }}</span>
                            </strong>
                        </td>
                    </tr>
                    <tr style=''>
                        <td width="9%" colspan=2 valign=top
                            style='width:9.72%;border-top:none; border-left:none; padding:0cm 5.4pt 0cm 5.4pt;'>
                            <p class=MsoNormal align=right
                                style='margin-bottom:0cm;text-align:right; text-indent:9.0pt;line-height:normal'>
                                <span lang=EN-US style='font-size: 9.0pt;'><strong>DATE</strong></span>
                            </p>
                        </td>
                        <td width="17%" colspan=3 valign=center
                            style='width:17.76%;border-top:none; border-left:none; padding:0cm 5.4pt 0cm 5.4pt '>
                            <p class=MsoNormal align=right
                                style='margin-bottom:0cm;text-align:right; text-indent:9.0pt;line-height:normal'>
                                <span lang=EN-US
                                    style='font-size: 9.0pt;'><strong>{{ Carbon\Carbon::parse($check->check_date)->format('m-d-Y') }}</strong></span>
                            </p>
                        </td>
                        <td width="7%" nowrap colspan=3 valign=bottom
                            style='width:7.1%;border-top: none;border-left:none; padding:0cm 5.4pt 0cm 5.4pt'>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                            <p class='MsoNormal'>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td nowrap colspan=21 valign=bottom
                            style=' border-top:none;padding:0cm 5.4pt 0cm 5.4pt; height:10pt'>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">

                        </td>
                    </tr>
                    <tr>
                        <td width="70%" colspan=14 valign=bottom
                            style='border-top:none; border-left:none; padding:0cm 5.4pt 0cm 5.4pt; border-bottom: 1.2px solid black;'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal; font-weight: bold;'>
                                Pay ****{{ strtoupper($check->amount_in_words) }} US
                                DOLLARS
                            </p>
                        </td>
                        <td width="30%" nowrap colspan=5 valign=bottom
                            style=' border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;'>
                            <p class=MsoNormal
                                style='margin-bottom:0cm;line-height:normal; padding: 2pt; text-align: right;'>
                                <span lang=EN-US
                                    style='font-size:12.0pt;color:black; font-weight: bold;'>****${{ number_format($check->amount, 2) }}</span>
                            </p>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                            <p class='MsoNormal'>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td width="82%" nowrap colspan=15 valign=bottom style=''>

                        </td>
                        <td width="16%" nowrap colspan=6 valign=top style=''>
                            <p class=MsoNormal style='margin-bottom:0cm; text-align: center;height:15.4pt'>
                                <span lang=EN-US style='font-size:9.5pt;color:black'>VOID AFTER 120 DAYS</span>
                            </p>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">

                        </td>
                    </tr>
                    <tr>
                        <td width="4%" nowrap valign=bottom
                            style='width:4.2%; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'></p>
                        </td>
                        <td nowrap colspan=8 valign=top rowspan=3
                            style=' border-top:none;padding:0cm 5.4pt 0cm 5.4pt; height:25pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal;font-weight: bold;'>
                                to the Order of:<br>
                                {{ $check->pay_to }}<br>
                                @if (!empty($check->address))
                                    {{ $check->address }}<br>
                                    {{ strtoupper($check->city) }}, {{ $check->state }} {{ $check->zip }}<br>
                                    USA
                                @else
                                    &nbsp;<br>
                                    &nbsp;<br>
                                    &nbsp;
                                @endif
                            </p>
                        </td>
                        <td nowrap colspan=12 valign=bottom
                            style=' border-top:none;padding:0cm 5.4pt 0cm 5.4pt; height:25pt'>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                            <p class='MsoNormal'>&nbsp; </p>
                        </td>
                    </tr>

                    <tr style=''>
                        <td width="4%" nowrap valign=bottom
                            style='width:4.2%; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'></p>
                        </td>

                        <td width="30%" nowrap colspan=2 valign=bottom
                            style='width:30.66%; border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt;'>
                        </td>
                        <td width="34%" nowrap colspan=8 valign=top
                            style='width:34.58%; border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt; border-bottom: 1.2px solid black;'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='color:black'>&nbsp;</span>
                            </p>
                        </td>
                        <td style='border:none;padding:0cm 0cm 0cm 0cm;' width="0%" colspan=2>
                            <p class='MsoNormal'>&nbsp; </p>
                        </td>
                    </tr>

                    <tr>
                        <td width="4%" nowrap valign=bottom
                            style='width:4.2%; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'></p>
                        </td>

                        <td width="30%" nowrap colspan=2 valign=bottom style=''>
                        </td>
                        <td width="99%" nowrap colspan=8 valign=top style='text-align: center; font-size: 9.5pt;'>
                            Authorized Signature
                        </td>
                        <td style='border:none;' width="0%">
                            <p class='msoFalse'>&nbsp; </p>
                        </td>
                    </tr>
                    <td nowrap colspan=21 valign=bottom
                        style=' border-top:none;padding:0cm 5.4pt 0cm 5.4pt; height:25pt'>
                    </td>
                    <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">

                    </td>
                    <tr>
                        <td width="17%" nowrap colspan=2 valign=top style='width:17.18%; border-top:none'></td>
                        </td>
                        <td width="8%" colspan=11 valign=top
                            style='width:8.6%;border-top:none;border-left: none; text-align: right;'>
                            <p class='check-number' style=''>
                                <span style='font-size: 14.0pt;'>A263079276A</span>
                                <span style='font-size: 14.0pt;'>7505434465C</span>
                                <span style='font-size: 14.0pt;'>{{ $check->id }}</span>
                            </p>
                        </td>


                        <td width="2%" valign=top style='width:2.5%;border-top:none;border-left: none;'>
                            &nbsp;
                        </td>
                        <td width="15%" nowrap valign=top style='width:15.1%;border-top: none;border-left:none;'>

                        </td>

                        <td width="20%" valign=top style='width:20.06%;border-top:none; border-left:none; '>

                        </td>
                        <td width="2%" nowrap valign=top style='width:2.5%;border-top: none;border-left:none; '>
                        </td>
                        <td width="24%" nowrap colspan=3 valign=bottom
                            style='width:24.06%; border-top:none;border-left:none;'>
                        </td>
                    </tr>
                    <tr height=0>
                        <td width=32 style='border:none'></td>
                        <td width=6 style='border:none'></td>
                        <td width=42 style='border:none'></td>
                        <td width=52 style='border:none'></td>
                        <td width=15 style='border:none'></td>
                        <td width=15 style='border:none'></td>
                        <td width=15 style='border:none'></td>
                        <td width=9 style='border:none'></td>
                        <td width=107 style='border:none'></td>
                        <td width=15 style='border:none'></td>
                        <td width=15 style='border:none'></td>
                        <td width=74 style='border:none'></td>
                        <td width=1 style='border:none'></td>
                        <td width=52 style='border:none'></td>
                        <td width=9 style='border:none'></td>
                        <td width=6 style='border:none'></td>
                        <td width=25 style='border:none'></td>
                        <td width=39 style='border:none'></td>
                        <td width=25 style='border:none'></td>
                        <td width=0 style='border:none'></td>
                        <td width=0 style='border:none'></td>
                    </tr>
                </table>

                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                        style='font-size:9.0pt;color:black'>&nbsp;</span></p>

                <table border=0
                    style="margin-bottom: 60px; border-collapse:collapse;border:none; font-weight:bold; font-size: 9.0pt"
                    cellspacing=0 cellpadding=0 width='100%'>
                    <tr>
                        <td width=35%>{{ $check->pay_to }}</td>
                        <td style="text-align: left">{{ \Carbon\Carbon::parse($check->check_date)->format('M d, Y') }}
                        </td>
                        <td style="text-align: right">Check No. {{ $check->id }}</td>
                    </tr>
                </table>

                <table class=MsoNormalTable cellspacing=0 cellpadding=0 width='100%'
                    style='width:100.0%;border-collapse:collapse;border:none'>
                    <tr style="font-size: 9.0pt;">
                        <th style="text-align: left;" width=20%>Assignment No.</th>
                        <th style="text-align: left;" width=30%>Date of service provided</th>
                        <th style="text-align: left;" width=40%>Location</th>
                        <th style="text-align: right;">Net Amount</th>
                    </tr>
                    @if ($preview)
                        @foreach ($check->checkDetailPreviews as $checkDetail)
                            <tr style="font-size: 9.0pt">
                                <td>{{ $checkDetail->assignment_number }}</td>
                                <td>{{ Carbon\Carbon::parse($checkDetail->date_of_service_provided)->format('m-d-Y') }}
                                </td>
                                <td>{{ $checkDetail->location }}</td>
                                <td style="text-align: right;">{{ number_format($checkDetail->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        @foreach ($check->checkDetails as $checkDetail)
                            <tr style="font-size: 9.0pt">
                                <td>{{ $checkDetail->assignment_number }}</td>
                                <td>{{ Carbon\Carbon::parse($checkDetail->date_of_service_provided)->format('m-d-Y') }}
                                </td>
                                <td>{{ $checkDetail->location }}</td>
                                <td style="text-align: right;">{{ number_format($checkDetail->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif


                    <tr style="font-size: 9.0pt;">
                        <td colspan=3 style="text-align: right; padding-top: 15px; padding-right:10px;"></td>
                        <td style="text-align: right; padding-top: 15px; padding-right:10px; width: 50%;">
                            <b>Total</b>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            {{ number_format($check->amount, 2) }}
                        </td>
                    </tr>
                </table>

            </div>
            @if (!$loop->last)
                <div class='page-break'></div>
            @endif
        @endforeach
    @else
        <h1 style="text-align: center; margin-top: 100px;">No checks found</h1>
    @endif




</body>

</html>
