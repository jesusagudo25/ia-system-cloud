<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $preview ? 'Preview' : 'Payroll' }}: {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
        {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}</title>
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


    @if (count($interpreters) > 0 && count($coordinator) > 0)
        <div class=WordSection1>

            @foreach ($interpreters as $interpreter)
                <div class=WordSection1>
                    <p class=MsoNormal align=center style='text-align:center'><b>
                            <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                                {{ $preview ? 'Preview' : 'Payroll' }}:
                                {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
                                {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}
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
                            <td width="100%" colspan=4
                                style='width:100.0%;border:solid windowtext 1.0pt; background:#FBE4D5;padding:0cm 5.4pt 0cm 5.4pt;height:20.8pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                    <i>
                                        <span style='font-size:13.0pt;font-family:"Arial",sans-serif; color:black'>
                                            {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
                                            {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}
                                        </span>
                                    </i>
                                </p>
                            </td>
                        </tr>
                        <tr style='height:21.4pt'>
                            <td width="100%" colspan=4
                                style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:21.4pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                    <i><span style='font-size:12.0pt;font-family:"Arial",sans-serif'>Payment for
                                            interpreting Services</span></i>
                                </p>
                            </td>
                        </tr>
                        <tr style='height:13.35pt'>
                            <td width="100%" colspan=4
                                style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;background:#D9E2F3;padding:0cm 5.4pt 0cm 5.4pt;height:13.35pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                    <span lang=EN-US
                                        style='font-size:9.0pt;font-family:"Arial",sans-serif; color:black'>
                                        {{ $interpreter[0]['full_name'] }} {{ $interpreter[0]['address'] }},
                                        {{ $interpreter[0]['city'] }}, {{ $interpreter[0]['state'] }}
                                        {{ $interpreter[0]['zip_code'] }}
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr style='height:20.05pt'>
                            <td width="14%"
                                style='width:14.4%;border:solid windowtext 1.0pt;border-top: none;padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                                style='font-size:9.0pt;font-family:"Arial",sans-serif'>Date</span></i></b>
                                </p>
                            </td>
                            <td width="22%"
                                style='width:22.48%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                                style='font-size:9.0pt;font-family:"Arial",sans-serif'>Assignment
                                                #</span></i></b></p>
                            </td>
                            <td width="48%"
                                style='width:48.16%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                                style='font-size:9.0pt;font-family:"Arial",sans-serif'>Location</span></i></b>
                                </p>
                            </td>
                            <td width="14%"
                                style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                                <p class=MsoNormal align=center
                                    style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                            <span
                                                style='font-size:9.0pt;font-family:"Arial",sans-serif'>Amount</span></i></b>
                                </p>
                            </td>
                        </tr>

                        @php($totalInterpreter = 0)
                        @foreach ($interpreter as $assignment)
                            <tr>
                                <td width="14%"
                                    style='width:14.4%;border:solid windowtext 1.0pt;border-top: none;padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal align=right
                                        style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                        <span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ Carbon\Carbon::parse($assignment['date_of_service_provided'])->format('m-d-Y') }}</span>
                                    </p>
                                </td>
                                <td width="22%"
                                    style='width:22.48%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal align=right
                                        style='margin-bottom:0cm;text-align:right; line-height:normal'><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['assignment_number'] }}</span>
                                    </p>
                                </td>
                                <td width="48%"
                                    style='width:48.16%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['locationAddress'] . ', ' . $assignment['locationCity'] . ', ' . $assignment['locationState'] . ', ' . $assignment['locationZipCode'] }}</span>
                                </td>
                                <td width="14%"
                                    style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal align=right
                                        style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                        <span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['total_interpreter'] }}</span>
                                        @php($totalInterpreter += $assignment['total_interpreter'])
                                    </p>
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td width="14%" valign=top
                                style='width:14.4%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                        style='font-family:"Arial",sans-serif'>&nbsp;</span></p>
                            </td>
                            <td width="22%" valign=top
                                style='width:22.48%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                    <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                                </p>
                            </td>
                            <td width="48%" valign=top
                                style='width:48.16%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                    <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                                </p>
                            </td>
                            <td width="14%" valign=top
                                style='width:14.96%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                    <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td width="35%" colspan=2 valign=top
                                style='width:35.48%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                        style='font-family:"Arial",sans-serif'>&nbsp;</span></p>
                            </td>
                            <td width="47%" valign=top
                                style='width:47.96%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; background:#E2EFD9;padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal align=right
                                    style='margin-bottom:0cm;text-align:right; line-height:normal'><b><i><span
                                                style='font-family:"Arial",sans-serif; color:black'>TOTAL</span></i></b>
                                </p>
                            </td>
                            <td width="16%"
                                style='width:16.56%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; background:#E2EFD9;padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal align=right
                                    style='margin-bottom:0cm;text-align:right; line-height:normal'><b><i><span
                                                style='font-family:"Arial",sans-serif; color:black'>{{ number_format($totalInterpreter, 2) }}</span></i></b>
                                </p>
                            </td>
                        </tr>
                    </table>

                </div>
                <p class=MsoNormal>&nbsp;</p>

                <div class="page-break"></div>
            @endforeach

            <div class=WordSection1>
                <p class=MsoNormal align=center style='text-align:center'><b>
                        <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                            {{ $preview ? 'Preview' : 'Payroll' }}:
                            {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
                            {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}
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
                        <td width="100%" colspan=4
                            style='width:100.0%;border:solid windowtext 1.0pt; background:#FBE4D5;padding:0cm 5.4pt 0cm 5.4pt;height:20.8pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i>
                                    <span style='font-size:13.0pt;font-family:"Arial",sans-serif; color:black'>
                                        {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
                                        {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}
                                    </span>
                                </i>
                            </p>
                        </td>
                    </tr>

                    <tr style='height:21.4pt'>
                        <td width="100%" colspan=4
                            style='width:100.0%;border:solid windowtext 1.0pt border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:21.4pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i><span style='font-size:12.0pt;font-family:"Arial",sans-serif'>Payment for
                                        interpreting
                                        Services</span></i>
                            </p>
                        </td>
                    </tr>

                    <tr style='height:13.35pt'>
                        <td width="100%" colspan=4
                            style='width:100.0%;border:solid windowtext 1.0pt; border-top:none;background:#D9E2F3;padding:0cm 5.4pt 0cm 5.4pt;height:13.35pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><span lang=EN-US
                                    style='font-size:9.0pt;font-family:"Arial",sans-serif; color:black'>{{ $coordinator[0]->full_name }}
                                    {{ $coordinator[0]->address }}, {{ $coordinator[0]->city }},
                                    {{ $coordinator[0]->state }} {{ $coordinator[0]->zip_code }}</span></p>
                        </td>
                    </tr>

                    <tr style='height:20.05pt'>
                        <td width="14%"
                            style='width:14.4%;border:solid windowtext 1.0pt;border-top: none;padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Date</span></i></b>
                            </p>
                        </td>
                        <td width="22%"
                            style='width:22.48%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Assignment
                                            #</span></i></b></p>
                        </td>
                        <td width="48%"
                            style='width:48.16%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Location</span></i></b>
                            </p>
                        </td>
                        <td width="14%"
                            style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                        <span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Amount</span></i></b>
                            </p>
                        </td>
                    </tr>

                    @php($totalCoordinator = 0)
                    @foreach ($coordinator as $assignment)
                        <tr>
                            <td width="14%"
                                style='width:14.4%;border:solid windowtext 1.0pt;border-top: none;padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal align=right
                                    style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                    <span
                                        style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ Carbon\Carbon::parse($assignment['date_of_service_provided'])->format('m-d-Y') }}</span>
                                </p>
                            </td>
                            <td width="22%"
                                style='width:22.48%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal align=right
                                    style='margin-bottom:0cm;text-align:right; line-height:normal'><span
                                        style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['assignment_number'] }}</span>
                                </p>
                            </td>
                            <td width="48%"
                                style='width:48.16%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                        style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['locationAddress'] . ', ' . $assignment['locationCity'] . ', ' . $assignment['locationState'] . ', ' . $assignment['locationZipCode'] }}</span>
                                </p>
                            </td>
                            <td width="14%"
                                style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                <p class=MsoNormal align=right
                                    style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                    <span
                                        style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['total_coordinator'] }}</span>
                                    @php($totalCoordinator += $assignment['total_coordinator'])
                                </p>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td width="14%" valign=top
                            style='width:14.4%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                    style='font-family:"Arial",sans-serif'>&nbsp;</span></p>
                        </td>
                        <td width="22%" valign=top
                            style='width:22.48%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                            </p>
                        </td>
                        <td width="48%" valign=top
                            style='width:48.16%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                            </p>
                        </td>
                        <td width="14%" valign=top
                            style='width:14.96%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td width="36%" colspan=2 valign=top
                            style='width:36.88%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'> <span
                                    style='font-family:"Arial",sans-serif'>&nbsp;</span></p>
                        </td>
                        <td width="48%" valign=top
                            style='width:48.16%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; background:#E2EFD9;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal align=right
                                style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                <b><i>
                                        <span style='font-family:"Arial",sans-serif; color:black'>TOTAL</span></i></b>
                            </p>
                        </td>
                        <td width="14%"
                            style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; background:#E2EFD9;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal align=right
                                style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                <b><i>
                                        <span
                                            style='font-family:"Arial",sans-serif; color:black'>{{ number_format($totalCoordinator, 2) }}</span></i></b>
                            </p>
                        </td>
                    </tr>
                </table>

            </div>

            <p class=MsoNormal>&nbsp;</p>

            <div class="page-break"></div>

            {{-- Miscellaneous --}}

            <div class=WordSection1>
                <p class=MsoNormal align=center style='text-align:center'><b>
                        <span style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>
                            {{ $preview ? 'Preview' : 'Payroll' }}:
                            {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
                            {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}
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
                        style='font-size:12.0pt;line-height:107%;font-family:"Arial",sans-serif'>Miscellaneous</span></b>
            </p>

            <div align=center>

                <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width="100%"
                    style='width:100.0%;border-collapse:collapse;border:none'>

                    <tr style='height:20.8pt'>
                        <td width="100%" colspan=4
                            style='width:100.0%;border:solid windowtext 1.0pt; background:#FBE4D5;padding:0cm 5.4pt 0cm 5.4pt;height:20.8pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i>
                                    <span style='font-size:13.0pt;font-family:"Arial",sans-serif; color:black'>
                                        {{ Carbon\Carbon::parse($payroll->start_date)->format('m-d-Y') }} to
                                        {{ Carbon\Carbon::parse($payroll->end_date)->format('m-d-Y') }}
                                    </span>
                                </i>
                            </p>
                        </td>
                    </tr>

                    <tr style='height:21.4pt'>
                        <td width="100%" colspan=4
                            style='width:100.0%;border:solid windowtext 1.0pt border-top:none;padding:0cm 5.4pt 0cm 5.4pt;height:21.4pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'>
                                <i><span style='font-size:12.0pt;font-family:"Arial",sans-serif'>
                                        List of miscellaneous payments</span></i>
                                </span></i>
                            </p>
                        </td>
                    </tr>

                    <tr style='height:20.05pt'>
                        <td width="14%"
                            style='width:14.4%;border:solid windowtext 1.0pt;border-top: none;padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Date</span></i></b>
                            </p>
                        </td>
                        <td width="22%"
                            style='width:22.48%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Assignment
                                            #</span></i></b></p>
                        </td>
                        <td width="48%"
                            style='width:48.16%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Location</span></i></b>
                            </p>
                        </td>
                        <td width="14%"
                            style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt;height:20.05pt'>
                            <p class=MsoNormal align=center
                                style='margin-bottom:0cm;text-align:center; line-height:normal'><b><i>
                                        <span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>Amount</span></i></b>
                            </p>
                        </td>
                    </tr>

                    @php($totalMiscellaneous = 0)
                    @if (count($miscellaneous) > 0)
                        @foreach ($miscellaneous as $assignment)
                            <tr>
                                <td width="14%"
                                    style='width:14.4%;border:solid windowtext 1.0pt;border-top: none;padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal align=right
                                        style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                        <span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ Carbon\Carbon::parse($assignment['date_of_service_provided'])->format('m-d-Y') }}</span>
                                    </p>
                                </td>
                                <td width="22%"
                                    style='width:22.48%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal align=right
                                        style='margin-bottom:0cm;text-align:right; line-height:normal'><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['assignment_number'] }}</span>
                                    </p>
                                </td>
                                <td width="48%"
                                    style='width:48.16%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['locationAddress'] . ', ' . $assignment['locationCity'] . ', ' . $assignment['locationState'] . ', ' . $assignment['locationZipCode'] }}</span>
                                    </p>
                                </td>
                                <td width="14%"
                                    style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                                    <p class=MsoNormal align=right
                                        style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                        <span
                                            style='font-size:9.0pt;font-family:"Arial",sans-serif'>{{ $assignment['miscellaneous'] }}</span>
                                        @php($totalMiscellaneous += abs($assignment['miscellaneous']))
                                    </p>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" valign=top
                            style='width:14.4%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt; text-align:center;'><p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                style='font-family:"Arial",sans-serif'>No data found</span></p></td>
                        </tr>
                    @endif


                    <tr>
                        <td width="14%" valign=top
                            style='width:14.4%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><span
                                    style='font-family:"Arial",sans-serif'>&nbsp;</span></p>
                        </td>
                        <td width="22%" valign=top
                            style='width:22.48%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                            </p>
                        </td>
                        <td width="48%" valign=top
                            style='width:48.16%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                            </p>
                        </td>
                        <td width="14%" valign=top
                            style='width:14.96%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'>
                                <span style='font-family:"Arial",sans-serif'>&nbsp;</span>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td width="36%" colspan=2 valign=top
                            style='width:36.88%;border:solid windowtext 1.0pt; border-top:none;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'> <span
                                    style='font-family:"Arial",sans-serif'>&nbsp;</span></p>
                        </td>
                        <td width="48%" valign=top
                            style='width:48.16%;border-top:none;border-left: none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; background:#E2EFD9;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal align=right
                                style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                <b><i>
                                        <span style='font-family:"Arial",sans-serif; color:black'>TOTAL</span></i></b>
                            </p>
                        </td>
                        <td width="14%"
                            style='width:14.96%;border-top:none;border-left:none; border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt; background:#E2EFD9;padding:0cm 5.4pt 0cm 5.4pt'>
                            <p class=MsoNormal align=right
                                style='margin-bottom:0cm;text-align:right; line-height:normal'>
                                <b><i>
                                        <span
                                            style='font-family:"Arial",sans-serif; color:black'>{{ number_format($totalMiscellaneous, 2) }}</span></i></b>
                            </p>
                        </td>
                    </tr>
                </table>

            </div>

            <p class=MsoNormal>&nbsp;</p>
        </div>
    @else
        <h1 style="text-align: center; margin-top: 100px;">No data found</h1>
    @endif


</body>

</html>
