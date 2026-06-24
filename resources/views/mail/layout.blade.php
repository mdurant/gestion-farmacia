@php
    $tones = [
        'primary' => ['accent' => '#7367f0', 'soft' => '#f3f1fe', 'border' => '#dcd6ff'],
        'success' => ['accent' => '#28c76f', 'soft' => '#eefbf3', 'border' => '#b9efd0'],
        'warning' => ['accent' => '#ff9f43', 'soft' => '#fff7ef', 'border' => '#ffd9b0'],
        'error' => ['accent' => '#ea5455', 'soft' => '#fff0f0', 'border' => '#ffcaca'],
        'info' => ['accent' => '#00cfe8', 'soft' => '#eefcfd', 'border' => '#b8eef5'],
    ];
    $palette = $tones[$tone ?? 'primary'] ?? $tones['primary'];
@endphp
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $headline ?? $appName }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f8f8f8;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;">
        {{ $preheader ?? '' }}
    </span>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8f8f8;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;">

                    {{-- Header / marca --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#7367f0 0%,#9e95f5 100%);border-radius:14px 14px 0 0;padding:28px 32px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td>
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="width:44px;height:44px;background:rgba(255,255,255,0.18);border-radius:12px;text-align:center;vertical-align:middle;font-size:22px;line-height:44px;">
                                                    &#9879;
                                                </td>
                                                <td style="padding-left:14px;">
                                                    <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;line-height:1.2;">
                                                        {{ $appName ?? 'Acalis Pharma' }}
                                                    </p>
                                                    <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,0.82);">
                                                        Gestión farmacéutica · Residencias de larga estadía
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Contenido --}}
                    <tr>
                        <td style="background-color:#ffffff;border-left:1px solid #ebe9f1;border-right:1px solid #ebe9f1;padding:32px;">
                            @if (! empty($headline))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
                                    <tr>
                                        <td style="border-left:4px solid {{ $palette['accent'] }};padding-left:14px;">
                                            <h1 style="margin:0;font-size:22px;font-weight:700;color:#5e5873;line-height:1.3;">
                                                {{ $headline }}
                                            </h1>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @yield('mail-content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid #ebe9f1;border-top:none;border-radius:0 0 14px 14px;padding:0 32px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="border-top:1px solid #ebe9f1;padding-top:20px;">
                                        <p style="margin:0 0 8px;font-size:12px;line-height:1.6;color:#a8a4b8;text-align:center;">
                                            Este es un mensaje automático del sistema {{ $appName ?? 'Acalis Pharma' }}.
                                            <br>No responda a este correo.
                                        </p>
                                        @if (! empty($appUrl))
                                            <p style="margin:0;font-size:12px;color:#7367f0;text-align:center;">
                                                <a href="{{ $appUrl }}" style="color:#7367f0;text-decoration:none;">{{ $appUrl }}</a>
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 8px 0;text-align:center;">
                            <p style="margin:0;font-size:11px;color:#b9b5c3;">
                                &copy; {{ date('Y') }} {{ $appName ?? 'Acalis Pharma' }} · Chile
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
