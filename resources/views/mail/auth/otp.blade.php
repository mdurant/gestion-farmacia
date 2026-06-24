@extends('mail.layout')

@section('mail-content')
    <p style="margin:0 0 8px;font-size:15px;font-weight:600;color:#5e5873;">
        {{ $greeting }}
    </p>

    <p style="margin:0 0 24px;font-size:15px;line-height:1.65;color:#6e6b7b;">
        {{ $intro }}
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center" style="padding:24px 16px;background:linear-gradient(135deg,#7367f0 0%,#9e95f5 100%);border-radius:14px;">
                <p style="margin:0 0 8px;font-size:12px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.85);">
                    Código de activación
                </p>
                <p style="margin:0;font-size:36px;font-weight:700;letter-spacing:0.35em;color:#ffffff;font-family:'Courier New',Courier,monospace;">
                    {{ $otpCode }}
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:8px;background:#f3f1fe;border:1px solid #dcd6ff;">
                <a href="{{ $actionUrl }}"
                   style="display:inline-block;padding:13px 28px;font-size:14px;font-weight:600;color:#7367f0;text-decoration:none;border-radius:8px;">
                    Activar mi cuenta
                </a>
            </td>
        </tr>
    </table>

    @if (! empty($footnote))
        <p style="margin:0;font-size:13px;line-height:1.6;color:#a8a4b8;text-align:center;">
            {{ $footnote }}
        </p>
    @else
        <p style="margin:0;font-size:13px;line-height:1.6;color:#a8a4b8;text-align:center;">
            El código expira en {{ $otpTtlMinutes ?? 15 }} minutos. No comparta este código con nadie.
        </p>
    @endif
@endsection
