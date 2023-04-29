<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bank Checks</title>
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

        .msoFalse {
            margin-top: 0cm;
            margin-right: 0cm;
            margin-bottom: 10.0pt;
            margin-left: 0cm;
            line-height: 85%;
            font-size: 0.5pt;
        }

        p.MsoBold{
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

        .check-number{
            font-family: 'micr-e13b';
        }

        .id-number{
            font-family: 'Calibri-Bold';
        }

        .page-break {
            page-break-after: always;
        }

    </style>
</head>

<body lang=ES-PA style='word-wrap:break-word'>

    @foreach ($checks as $check)
    <div class=WordSection1>

        <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
            style='width:100.0%;border-collapse:collapse;border:none'>
            <tr style=''>
                <td width="65%" nowrap colspan=13 rowspan=2 valign=top style='width:65.22%; padding:0cm 5.4pt 0cm 5.4pt;'>
                    <p class=MsoBold style='margin-bottom:0;' ><span lang=EN-US style='color:black; font-size: 14.5px'>INTERPRETERS ACADEMY</span></p>
                    <p class=MsoBold style='margin-bottom:0;'><span lang=EN-US style='color:black; font-size: 14.5px;'>OF JACKSONVILLE LLC</span></p>
                    <p class=MsoNormal style='margin-bottom:0;'><span lang=EN-US style='font-size:10.0pt;color:black; font-size: 13.5px;'>2280 SHEPARD ST APT 404</span></p>
                    <p class=MsoNormal style='margin-bottom:0;'><span lang=EN-US style='font-size:10.0pt;color:black; font-size: 13.5px;'>JACKSONVILLE, FL 32211-3284</span></p>
                </td>
                    <p class=MsoNormal style='margin-bottom:0;line-height:normal'><b><span lang=EN-US style='color:black'>&nbsp;</span></b></p>
                </td>
                <td width="16%" nowrap colspan=3 valign=bottom style='width:16.82%; border-left:none;padding:0cm 5.4pt 0cm 5.4pt; '>
                    <p class='MsoNormal'>&nbsp; </p>
                </td>
                <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                    <p class='MsoNormal'>&nbsp; </p>
                </td>
                <td width="16%" nowrap colspan=3 valign=bottom style='width:16.82%; border-left:none;padding:0cm 5.4pt 0cm 5.4pt; height:36.0pt'>
                    <p class='id-number' align=right style='margin-bottom:0cm;text-align:right; line-height:normal'>
                        <span lang=EN-US style='font-size:14.0pt;color:black'>{{ $check->id }}</span>
                    </p>
                </td>
            </tr>
            <tr style=''>
                <td width="9%" colspan=2 valign=top style='width:9.72%;border-top:none; border-left:none; padding:0cm 5.4pt 0cm 5.4pt;'>
                    <p class=MsoNormal align=right style='margin-bottom:0cm;text-align:right; text-indent:8.0pt;line-height:normal'>
                        <i><span lang=EN-US style='font-size: 8.0pt;'>DATE</span></i>
                    </p>
                </td>
                <td width="17%" colspan=3 valign=top style='width:17.76%;border-top:none; border-left:none; padding:0cm 5.4pt 0cm 5.4pt '>
                    <p class=MsoNormal align=right style='margin-bottom:0cm;text-align:right; text-indent:8.0pt;line-height:normal'>
                        <i><span lang=EN-US style='font-size: 8.0pt;'>{{ $check->date }}</span></i>
                    </p>
                </td>
                <td width="7%" nowrap colspan=3 valign=bottom style='width:7.1%;border-top: none;border-left:none; padding:0cm 5.4pt 0cm 5.4pt'>
                </td>
                <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                    <p class='MsoNormal'>&nbsp;
                </td>
            </tr>
            <tr style=''>
                <td width="6%" colspan=2 valign=top style='width:6.26%; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:70%'>
                        <span lang=EN-US style='font-size:10.0pt;color:black'>PAY</span>
                        </br>
                        <span lang=EN-US style='font-size:6.0pt;color:black'>TO THE </br> ORDER OF</span>
                    </p>
                </td>
                <td width="70%" colspan=14 valign=bottom style='border-top:none; border-left:none; padding:0cm 5.4pt 0cm 5.4pt; border-bottom: 0.1px solid black;'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                        <span lang=EN-US style='font-size:8.0pt;color:black'>{{ $check->pay_to }}</span>
                    </p>
                </td>
                <td width="30%" nowrap colspan=5 valign=bottom style=' border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal; border: 1.5px solid #d8d8d8; padding: 2pt'>
                        <span lang=EN-US style='font-size:12.0pt;color:black;'>$ {{ $check->amount }}</span>
                    </p>
                </td>
                <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                    <p class='MsoNormal'>&nbsp;
                </td>
            </tr>
            <tr style='height:30.85pt'>
                <td width="82%" nowrap colspan=15 valign=bottom style='width:82.96%; padding:0cm 5.4pt 0cm 5.4pt; height:30.85pt;
                border-bottom: 0.1px solid black;'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                        <span lang=EN-US style='color:black'>&nbsp;{{ $check->amount_in_words }}</span>
                    </p>
                </td>
                <td width="16%" nowrap colspan=6 valign=bottom
                    style='width:16.82%; border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:30.85pt'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                        <span lang=EN-US style='font-size:8.0pt;color:black'>Dollars</span>
                    </p>
                </td>
                <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                    <p class='MsoNormal'>&nbsp; </p>
                </td>
            </tr>
            <tr style='height:46.7pt'>
                <td  nowrap colspan=21 valign=bottom style=' border-top:none;padding:0cm 5.4pt 0cm 5.4pt; height:38pt'>
                </td>
                <td style='border:none;padding:0cm 0cm 0cm 0cm' width="0%">
                    <p class='MsoNormal'>&nbsp; </p>
                </td>
            </tr>
            <tr style='height:15.4pt'>
                <td width="4%" nowrap valign=bottom
                    style='width:4.2%; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span lang=EN-US
                            style='font-size:8.0pt;color:black'>FOR</span></p>
                </td>
                <td width="30%" nowrap colspan=7 valign=bottom style='width:30.26%; border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt; border-bottom: 0.1px solid black;'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                        <span lang=EN-US style='font-size:8.0pt;color:black'>{{ $check->for }}</span>
                    </p>
                </td>
                <td width="30%" nowrap colspan=4 valign=bottom
                    style='width:30.66%; border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt;'>
                </td>
                <td width="34%" nowrap colspan=9 valign=bottom style='width:34.58%; border-top:none;border-left:none;padding:0cm 5.4pt 0cm 5.4pt;height:15.4pt; border-bottom: 0.1px solid black;'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                        <span style='color:black'>&nbsp;</span>
                    </p>
                </td>
                <td style='border:none;padding:0cm 0cm 0cm 0cm; border-bottom: 0.1px solid black;' width="0%" colspan=2>
                    <p class='MsoNormal'>&nbsp; </p>
                </td>
            </tr>
            <tr>
                <td width="99%" nowrap colspan=21 valign=bottom style='width:99.8%; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;'>
                </td>
                <td style='border:none;' width="0%">
                    <p class='msoFalse'>&nbsp; </p>
                </td>
            </tr>
            <tr>
                <td width="17%" nowrap colspan=3 valign=top  style='width:17.18%; border-top:none'>
                </td>
                <td width="8%" valign=top style='width:8.6%;border-top:none;border-left: none;'>
                    <p class='check-number'  style=''>
                        <span style='font-size: 14.0pt;'>C1111C</span>
                    </p>
                </td>
                <td width="2%" nowrap valign=top style='width:2.5%;border-top:none; border-left:none;'>
                </td>
                <td width="2%" valign=bottom style='width:2.5%;border-top:none;border-left: none;'>
                    &nbsp;
                </td>
                <td width="2%" valign=top style='width:2.5%;border-top:none;border-left: none;'>
                    &nbsp;
                </td>
                <td width="15%" nowrap colspan=2 valign=top style='width:15.1%;border-top: none;border-left:none;'>
                    <p class='check-number'  style=''>
                        <span style='font-size: 14.0pt;'>A123456789A</span>
                    </p>
                </td>
                <td width="2%" valign=top style='width:2.5%;border-top:none;border-left: none;'>
                    &nbsp;
                </td>
                <td width="2%" valign=top style='width:2.5%;border-top:none;border-left: none;'>
                    &nbsp;
                </td>
                <td width="20%" colspan=3 valign=top style='width:20.06%;border-top:none; border-left:none; '>
                    <p class='check-number'  style=''>
                        <span style='font-size: 14.0pt;'>{{ $check->id }}</span>
                    </p>
                </td>
                <td width="2%" nowrap colspan=2 valign=top style='width:2.5%;border-top: none;border-left:none; '>
                </td>
                <td width="24%" nowrap colspan=5 valign=bottom style='width:24.06%; border-top:none;border-left:none;'>
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

    </div>
    @if (!$loop->last)
        <div class='page-break'></div>
    @endif
    @endforeach



</body>

</html>
