<!DOCTYPE html>
<html>

<head>
    <title>Invoice #{{ $invoice->id }}</title>
</head>
<style type="text/css">
    /* Font Definitions */
    @font-face {
        font-family: "Cambria Math";
        panose-1: 2 4 5 3 5 4 6 3 2 4;
    }

    @font-face {
        font-family: "Century Gothic";
        panose-1: 2 11 5 2 2 2 2 2 2 4;
    }

    @font-face {
        font-family: "Segoe UI";
        panose-1: 2 11 5 2 4 2 4 2 2 3;
    }

    @font-face {
        font-family: Consolas;
        panose-1: 2 11 6 9 2 2 4 3 2 4;
    }

    /* Style Definitions */
    p.MsoNormal,
    li.MsoNormal,
    div.MsoNormal {
        margin-top: 2.0pt;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    h1 {
        mso-style-link: "Heading 1 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        text-align: right;
        line-height: normal;
        font-size: 26.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        letter-spacing: 1.0pt;
        font-weight: bold;
    }

    h1.CxSpFirst {
        mso-style-link: "Heading 1 Char";
        margin: 0cm;
        text-align: right;
        line-height: normal;
        font-size: 26.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        letter-spacing: 1.0pt;
        font-weight: bold;
    }

    h1.CxSpMiddle {
        mso-style-link: "Heading 1 Char";
        margin: 0cm;
        text-align: right;
        line-height: normal;
        font-size: 26.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        letter-spacing: 1.0pt;
        font-weight: bold;
    }

    h1.CxSpLast {
        mso-style-link: "Heading 1 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        text-align: right;
        line-height: normal;
        font-size: 26.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        letter-spacing: 1.0pt;
        font-weight: bold;
    }

    h2 {
        mso-style-link: "Heading 2 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 6.0pt;
        margin-left: 0cm;
        line-height: normal;
        font-size: 14.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #595959;
        font-weight: bold;
    }

    h2.CxSpFirst {
        mso-style-link: "Heading 2 Char";
        margin: 0cm;
        line-height: normal;
        font-size: 14.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #595959;
        font-weight: bold;
    }

    h2.CxSpMiddle {
        mso-style-link: "Heading 2 Char";
        margin: 0cm;
        line-height: normal;
        font-size: 14.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #595959;
        font-weight: bold;
    }

    h2.CxSpLast {
        mso-style-link: "Heading 2 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 6.0pt;
        margin-left: 0cm;
        line-height: normal;
        font-size: 14.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #595959;
        font-weight: bold;
    }

    h3 {
        mso-style-link: "Heading 3 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: bold;
    }

    h3.CxSpFirst {
        mso-style-link: "Heading 3 Char";
        margin: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: bold;
    }

    h3.CxSpMiddle {
        mso-style-link: "Heading 3 Char";
        margin: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: bold;
    }

    h3.CxSpLast {
        mso-style-link: "Heading 3 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: bold;
    }

    h4 {
        mso-style-link: "Heading 4 Char";
        margin-top: 16.0pt;
        margin-right: 0cm;
        margin-bottom: 0cm;
        margin-left: 0cm;
        text-align: center;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: normal;
    }

    h4.CxSpFirst {
        mso-style-link: "Heading 4 Char";
        margin-top: 16.0pt;
        margin-right: 0cm;
        margin-bottom: 0cm;
        margin-left: 0cm;
        text-align: center;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: normal;
    }

    h4.CxSpMiddle {
        mso-style-link: "Heading 4 Char";
        margin: 0cm;
        text-align: center;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: normal;
    }

    h4.CxSpLast {
        mso-style-link: "Heading 4 Char";
        margin: 0cm;
        text-align: center;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: normal;
    }

    h5 {
        mso-style-link: "Heading 5 Char";
        margin-top: 2.0pt;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: 115%;
        page-break-after: avoid;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
        font-weight: normal;
    }

    h5.CxSpFirst {
        mso-style-link: "Heading 5 Char";
        margin-top: 2.0pt;
        margin-right: 0cm;
        margin-bottom: 0cm;
        margin-left: 0cm;
        line-height: 115%;
        page-break-after: avoid;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
        font-weight: normal;
    }

    h5.CxSpMiddle {
        mso-style-link: "Heading 5 Char";
        margin: 0cm;
        line-height: 115%;
        page-break-after: avoid;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
        font-weight: normal;
    }

    h5.CxSpLast {
        mso-style-link: "Heading 5 Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: 115%;
        page-break-after: avoid;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
        font-weight: normal;
    }

    p.MsoFooter,
    li.MsoFooter,
    div.MsoFooter {
        mso-style-link: "Footer Char";
        margin-top: 2.0pt;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        text-align: center;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
    }

    p.MsoClosing,
    li.MsoClosing,
    div.MsoClosing {
        mso-style-link: "Closing Char";
        margin-top: 10.0pt;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    p.MsoClosingCxSpFirst,
    li.MsoClosingCxSpFirst,
    div.MsoClosingCxSpFirst {
        mso-style-link: "Closing Char";
        margin-top: 10.0pt;
        margin-right: 0cm;
        margin-bottom: 0cm;
        margin-left: 0cm;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    p.MsoClosingCxSpMiddle,
    li.MsoClosingCxSpMiddle,
    div.MsoClosingCxSpMiddle {
        mso-style-link: "Closing Char";
        margin: 0cm;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    p.MsoClosingCxSpLast,
    li.MsoClosingCxSpLast,
    div.MsoClosingCxSpLast {
        mso-style-link: "Closing Char";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: 115%;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    strong {
        font-variant: normal !important;
        color: #2E74B5;
        text-transform: uppercase;
    }

    em {
        color: #2E74B5;
        font-weight: bold;
        font-style: normal;
    }

    span.MsoSubtleEmphasis {
        color: #404040;
        font-style: italic;
    }

    span.Heading1Char {
        mso-style-name: "Heading 1 Char";
        mso-style-link: "Heading 1";
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        letter-spacing: 1.0pt;
        font-weight: bold;
    }

    span.Heading2Char {
        mso-style-name: "Heading 2 Char";
        mso-style-link: "Heading 2";
        font-family: "Century Gothic", sans-serif;
        color: #595959;
        font-weight: bold;
    }

    span.Heading3Char {
        mso-style-name: "Heading 3 Char";
        mso-style-link: "Heading 3";
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
        font-weight: bold;
    }

    p.Cantidad,
    li.Cantidad,
    div.Cantidad {
        mso-style-name: Cantidad;
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        text-align: right;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    span.Heading4Char {
        mso-style-name: "Heading 4 Char";
        mso-style-link: "Heading 4";
        font-family: "Century Gothic", sans-serif;
        color: #2E74B5;
        text-transform: uppercase;
    }

    span.Heading5Char {
        mso-style-name: "Heading 5 Char";
        mso-style-link: "Heading 5";
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
    }

    span.FooterChar {
        mso-style-name: "Footer Char";
        mso-style-link: Footer;
        color: #2E74B5;
    }

    p.Alinearaladerecha,
    li.Alinearaladerecha,
    div.Alinearaladerecha {
        mso-style-name: "Alinear a la derecha";
        margin-top: 0cm;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        text-align: right;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    span.ClosingChar {
        mso-style-name: "Closing Char";
        mso-style-link: Closing;
        color: #404040;
    }

    p.Informacindecontacto,
    li.Informacindecontacto,
    div.Informacindecontacto {
        mso-style-name: "Información de contacto";
        margin: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
    }

    p.InformacindecontactoCxSpFirst,
    li.InformacindecontactoCxSpFirst,
    div.InformacindecontactoCxSpFirst {
        mso-style-name: "Información de contactoCxSpFirst";
        margin: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
    }

    p.InformacindecontactoCxSpMiddle,
    li.InformacindecontactoCxSpMiddle,
    div.InformacindecontactoCxSpMiddle {
        mso-style-name: "Información de contactoCxSpMiddle";
        margin: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
    }

    p.InformacindecontactoCxSpLast,
    li.InformacindecontactoCxSpLast,
    div.InformacindecontactoCxSpLast {
        mso-style-name: "Información de contactoCxSpLast";
        margin: 0cm;
        line-height: normal;
        font-size: 11.0pt;
        font-family: "Century Gothic", sans-serif;
        color: windowtext;
    }

    .MsoChpDefault {
        font-family: "Century Gothic", sans-serif;
        color: #404040;
    }

    .MsoPapDefault {
        margin-top: 2.0pt;
        margin-right: 0cm;
        margin-bottom: 2.0pt;
        margin-left: 0cm;
        line-height: 115%;
    }

    /* Page Definitions */
    @page WordSection1 {
        size: 21.0cm 842.0pt;
        margin: 36.0pt 36.0pt 36.0pt 36.0pt;
    }

    div.WordSection1 {
        page: WordSection1;
    }

    /* List Definitions */
    ol {
        margin-bottom: 0cm;
    }

    ul {
        margin-bottom: 0cm;
    }
</style>

<body>
    <div class=WordSection1>

        <table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 width="100%"
            style='width:100.0%;border-collapse:collapse'>
            <tr style='height:57.55pt'>
                <td width=408 valign=top style='width:306.05pt;padding:0cm 0cm 0cm 0cm;
          height:57.55pt'>
                    <h2><span lang=EN>Interpreters' Academy of Jacksonville</span></h2>
                </td>
                <td width=312 valign=top style='width:233.95pt;padding:0cm 0cm 0cm 0cm;
          height:57.55pt'>
                    <h1><span lang=EN>invoice</span></h1>
                </td>
            </tr>
            <tr style='height:67.45pt'>
                <td width=408 valign=top style='width:306.05pt;padding:0cm 0cm 0cm 0cm;
          height:67.45pt'>
                    <p class=InformacindecontactoCxSpFirst><span lang=EN>Please mail checks to:</span></p>
                    <p class=InformacindecontactoCxSpMiddle><span lang=EN>2280 Shepard St, #404 </span></p>
                    <p class=InformacindecontactoCxSpMiddle><span lang=EN>Jacksonville, FL 32211</span></p>
                    <p class=InformacindecontactoCxSpMiddle>&nbsp; </p>

                    <p class=InformacindecontactoCxSpLast><span lang=EN>Phone: 229.506.8207</span><span lang=EN>
                        </span></p>
                    <p class=InformacindecontactoCxSpLast><span
                            lang=EN>Interpreting@InterpretersAcademyofJax.com</span><span lang=EN>
                        </span></p>
                    <p class=InformacindecontactoCxSpMiddle>&nbsp; </p>
                </td>
                <td width=312 valign=top style='width:233.95pt;padding:0cm 0cm 0cm 0cm;
          height:67.45pt'>
                    <p class=Alinearaladerecha><strong><span lang=EN>INVOICE</span></strong><span
                            class=Heading3Char><span lang=EN> N.º </span></span><span lang=EN>{{$details->invoice_number}}</span></p>
                    <p class=Alinearaladerecha><span class=Heading3Char><span lang=EN> </span></span><strong><span
                                lang=EN>DATE</span></strong><span class=Heading3Char><span lang=EN> </span></span><span
                            lang=EN>{{\Carbon\Carbon::parse($details->date_of_service_provided)->format(' M d, Y')}}</span></p>
                </td>
            </tr>
            
            <tr style='height:93.55pt'>
                <td width=408 valign=top style='width:306.05pt;padding:0cm 0cm 14.4pt 0cm;
          height:93.55pt; border-top:1px'>
                    <p class=InformacindecontactoCxSpFirst><strong><span lang=EN><span
                                    style='color:windowtext;text-transform:none;font-weight:normal'>TO:</span></span></strong>
                    </p>
                    <p class=InformacindecontactoCxSpMiddle><span lang=EN>{{ $agency->name }}</span></p>
                    <p class=InformacindecontactoCxSpMiddle><span lang=EN>{{ $agency->address }}</span></p>
                    <p class=InformacindecontactoCxSpMiddle><span lang=EN>{{ $agency->city }}, {{ $agency->state }} {{ $agency->zip_code }}</span></p>
                </td>
                <td width=312 valign=top style='width:233.95pt;padding:0cm 0cm 0cm 0cm;
          height:93.55pt; border-top:1px'>
                    <p class=Alinearaladerecha><strong><span lang=EN>INTERPRETER/VENDOR ID # </span></strong><span
                            lang=EN> </span><span lang=EN>700830</span></p>
                </td>
            </tr>
        </table>

        <table class=MsoTable15Grid1LightAccent1 border=1 cellspacing=0 cellpadding=0 width="100%"
            style='width:100.0%;border-collapse:collapse;border:none'>
            <thead>
                <tr>
                    <td width=520 valign=top
                        style='width:389.9pt;border-top:solid #BDD6EE 1.0pt;
           border-left:none;border-bottom:solid #9CC2E5 1.5pt;border-right:none;
           padding:0cm 0cm 0cm 0cm'>
                        <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Description:
                                </span></em><span>{{ $description->title }}</span></h5>
                    </td>
                    <td width=179
                        style='width:134.6pt;border-top:solid #BDD6EE 1.0pt;
           border-left:none;border-bottom:solid #9CC2E5 1.5pt;border-right:none;
           padding:0cm 0cm 0cm 0cm'>
                        <h5 align=right style='margin-bottom:0cm;text-align:right;line-height:normal'><em><span
                                    lang=EN>Amount</span></em></h5>
                    </td>
                </tr>
            </thead>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Assignment:
                            </span></em><span>{{ $details->assignment_number,  }}</span></h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Date of Service Provided:
                            </span></em><span> {{\Carbon\Carbon::parse($details->date_of_service_provided)->format('m-d-Y') }}</span></h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>
                        <em><span lang=EN>Arrival time:</span></em>
                        <span>{{ $details->arrival_time ? \Carbon\Carbon::parse($details->arrival_time)->format('h:i A') : 'N/A' }}</span>
                        <em><span lang=EN>Start time:</span></em>
                        <span>{{ $details->start_time ? \Carbon\Carbon::parse($details->start_time)->format('h:i A') : 'N/A' }}</span>
                        <em><span lang=EN>End time:</span></em>
                        <span>{{ $details->end_time ? \Carbon\Carbon::parse($details->end_time)->format('h:i A') : 'N/A' }}</span>
                    </h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>
                        <em><span lang=EN>Travel time to assignment: </span></em>
                        <span>{{ $details->travel_time_to_assignment }} minutes</span>
                    </h5>
                </td>
                <td width=179 valign=top
                    style='width:100.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>
                        <em><span lang=EN>Time back from assignment: </span></em>
                        <span>{{ $details->time_back_from_assignment }} minutes</span>
                    </h5>
                </td>
                <td width=179 valign=top
                    style='width:100.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Travel mileage (round trip
                                total):
                            </span></em><span> {{ $details->travel_mileage }} miles</span></h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>

            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>&nbsp;</h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>

            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Location:
                            </span></em><span>{{ $details->address->address }}</span></h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>

            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>
                        <em><span lang=EN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </span></em><span>{{ $details->address->city }}, {{ $details->address->state }} {{ $details->address->zip_code }}</span>
                        </h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>&nbsp;</h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Comments:
                            </span></em><span>{{ $details->comments }}</span></h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>

            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>&nbsp;</h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN> </span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'>Mileage: {{ $details->travel_mileage }} miles @ ${{ $details->cost_per_mile }} per mile</h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN>${{ $details->total_amount_miles }}</span></p>
                </td>
            </tr>
            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <h5 style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>
                            Interpreting Service Rates:</span></em> ${{ $lenguage->price_per_hour }} per hour for two (2) hours Minimum</h5>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none;border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><span lang=EN>${{ $details->total_amount_hours }}</span></p>
                </td>
            </tr>

            <tr>
                <td width=520 valign=top
                    style='width:389.9pt;border:none; border-top:solid #9CC2E5 1.5pt; border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=MsoNormal style='margin-bottom:0cm;line-height:normal'><em><span lang=EN>Total amount Due:</span></em> <span lang=EN></span></p>
                    </p>
                </td>
                <td width=179 valign=top
                    style='width:134.6pt;border:none; border-top:solid #9CC2E5 1.5pt; border-bottom:solid #BDD6EE 1.0pt;
          padding:0cm 0cm 0cm 0cm'>
                    <p class=Cantidad style='margin-bottom:0cm'><em><span lang=EN>${{ $invoice->total_amount }}</span></em></p>
                </td>
            </tr>
        </table>

        <h4><span lang=EN>Thank you for your business!</span></h4>

    </div>
</body>

</html>
