<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Monthly Report: {{ Carbon\Carbon::parse($report->start_date)->format('m/d/Y') }} to
        {{ Carbon\Carbon::parse($report->end_date)->format('m/d/Y') }}</title>
    <style>
        /* Font Definitions */
        @font-face {
            font-family: "Cambria Math";
            panose-1: 2 4 5 3 5 4 6 3 2 4;
        }

        @font-face {
            font-family: Calibri;
            panose-1: 2 15 5 2 2 2 4 3 2 4;
        }

        /* Style Definitions */
        p.MsoNormal,
        li.MsoNormal,
        div.MsoNormal {
            margin-top: 0cm;
            margin-right: 0cm;
            margin-bottom: 8.0pt;
            margin-left: 0cm;
            line-height: 107%;
            font-size: 11.0pt;
            font-family: "Calibri", sans-serif;
        }

        .MsoChpDefault {
            font-family: "Calibri", sans-serif;
        }

        .MsoPapDefault {
            margin-bottom: 8.0pt;
            line-height: 107%;
        }

        /* Page Definitions */
        @page WordSection1 {
            size: 612.0pt 792.0pt;
            margin: 70.85pt 3.0cm 70.85pt 3.0cm;
        }

        div.WordSection1 {
            page: WordSection1;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body lang=ES-PA style='word-wrap:break-word'>

    <div class=WordSection1>
        @foreach ($reportsInterpreters as $reportList)
            <div class=WordSection1>
                <p class=MsoNormal align=center style='text-align:center'><b>
                        <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                            Monthly Report: {{ Carbon\Carbon::parse($report->start_date)->format('m/d/Y') }} to
                            {{ Carbon\Carbon::parse($report->end_date)->format('m/d/Y') }}
                        </span></b>
                </p>
                <hr>
                <p class=MsoNormal align=center style='text-align:center'><b>
                        <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                            &nbsp;
                        </span>
                    </b>
                </p>

            </div>

            <p class=MsoNormal align=center style='text-align:center'><b><span
                        style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>Interpreters</span></b>
            </p>

            <div align=center>

                <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
                    style='width:100.0%;border-collapse:collapse;border:none'>
                    <tr style='height:20.8pt'>
                        <td width="100%" colspan=13
                            style='width:100.0%;border:solid windowtext 1.0pt; background:#FBE4D5;padding:0cm 5.4pt 0cm 5.4pt;height:20.8pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i>
                                    <span style='font-size:13.0pt;font-family:"Arial",sans-serif; color:black'>
                                        {{ $reportList['name'] }}
                                    </span>
                                </i>
                            </p>
                        </td>
                    </tr>
                    <tr style='height:21.4pt'>
                        <td width="100%" colspan=13
                            style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:21.4pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i><span
                                        style='font-size:12.0pt;font-family:"Arial",sans-serif'>{{ $reportList['ssn'] }}</span></i>
                            </p>
                        </td>
                    </tr>
                    <tr style='height:13.35pt'>
                        <td width="100%" colspan=13
                            style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;background:#D9E2F3;padding:0cm 5.4pt 0cm 5.4pt;height:13.35pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <span lang=EN-US style='font-size:9.0pt;font-family:"Arial",sans-serif; color:black'>
                                    {{ $reportList['address'] }} {{ $reportList['city'] }} {{ $reportList['state'] }}
                                    {{ $reportList['zip_code'] }}
                                </span>
                            </p>
                        </td>
                    </tr>
                    <tr style='height:20.05pt'>
                        @foreach ($months as $month)
                            <td width="14%"
                                style='width:14.96%;border-top:none;border-left:solid window; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                            <span
                                                style='font-size:6.0pt;font-family:"Arial",sans-serif'>{{ $month }}</span></i></b>
                                </p>
                            </td>
                        @endforeach
                        <td width="14%"
                            style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                        <span
                                            style='font-size:6.0pt;font-family:"Arial",sans-serif'>Total</span></i></b>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        @foreach ($months as $month)
                            <td width="22%" valign=top
                                style='width:22.48%;border-top:none;border-left: solid window;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                    <span style='font-size:6.0pt;font-family:"Arial",sans-serif'>{{ number_format($reportList[$month], 2) }}</span>
                                </p>
                            </td>
                        @endforeach
                        <td width="22%" valign=top
                            style='width:22.48%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-size:6.0pt;font-family:"Arial",sans-serif'>{{ number_format($reportList['total'], 2) }}</span>
                            </p>
                        </td>
                    </tr>
                </table>

            </div>
            <p class=MsoNormal>&nbsp;</p>

            <div class="page-break"></div>
        @endforeach
    </div>

    <div class=WordSection1>
        @foreach ($reportsCoordinators as $reportList)
            <div class=WordSection1>
                <p class=MsoNormal align=center style='text-align:center'><b>
                        <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                            Monthly Report: {{ Carbon\Carbon::parse($report->start_date)->format('m/d/Y') }} to
                            {{ Carbon\Carbon::parse($report->end_date)->format('m/d/Y') }}
                        </span></b>
                </p>
                <hr>
                <p class=MsoNormal align=center style='text-align:center'><b>
                        <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                            &nbsp;
                        </span>
                    </b>
                </p>

            </div>

            <p class=MsoNormal align=center style='text-align:center'><b><span
                        style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>Coordinator</span></b>
            </p>

            <div align=center>

                <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
                    style='width:100.0%;border-collapse:collapse;border:none'>
                    <tr style='height:20.8pt'>
                        <td width="100%" colspan=13
                            style='width:100.0%;border:solid windowtext 1.0pt; background:#FBE4D5;padding:0cm 5.4pt 0cm 5.4pt;height:20.8pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i>
                                    <span style='font-size:13.0pt;font-family:"Arial",sans-serif; color:black'>
                                        {{ $reportList['name'] }}
                                    </span>
                                </i>
                            </p>
                        </td>
                    </tr>
                    <tr style='height:21.4pt'>
                        <td width="100%" colspan=13
                            style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:21.4pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i><span
                                        style='font-size:12.0pt;font-family:"Arial",sans-serif'>{{ $reportList['ssn'] }}</span></i>
                            </p>
                        </td>
                    </tr>
                    <tr style='height:13.35pt'>
                        <td width="100%" colspan=13
                            style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;background:#D9E2F3;padding:0cm 5.4pt 0cm 5.4pt;height:13.35pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <span lang=EN-US style='font-size:9.0pt;font-family:"Arial",sans-serif; color:black'>
                                    {{ $reportList['address'] }} {{ $reportList['city'] }} {{ $reportList['state'] }}
                                    {{ $reportList['zip_code'] }}
                                </span>
                            </p>
                        </td>
                    </tr>
                    <tr style='height:20.05pt'>
                        @foreach ($months as $month)
                            <td width="14%"
                                style='width:14.96%;border-top:none;border-left:solid window; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                            <span
                                                style='font-size:6.0pt;font-family:"Arial",sans-serif'>{{ $month }}</span></i></b>
                                </p>
                            </td>
                        @endforeach
                        <td width="14%"
                            style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                        <span
                                            style='font-size:6.0pt;font-family:"Arial",sans-serif'>Total</span></i></b>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        @foreach ($months as $month)
                            <td width="22%" valign=top
                                style='width:22.48%;border-top:none;border-left: solid window;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                    <span style='font-size:6.0pt; font-family:"Arial",sans-serif'>{{ number_format($reportList[$month], 2) }}</span>
                                </p>
                            </td>
                        @endforeach
                        <td width="22%" valign=top
                            style='width:22.48%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-size:6.0pt; font-family:"Arial",sans-serif'>{{ number_format($reportList['total'], 2) }}</span>
                            </p>
                        </td>
                    </tr>
                </table>

            </div>
            <p class=MsoNormal>&nbsp;</p>

        @endforeach
    </div>
</body>

</html>
