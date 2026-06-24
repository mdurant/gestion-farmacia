@extends('mail.layout')

@section('mail-content')
    <p style="margin:0 0 8px;font-size:15px;font-weight:600;color:#5e5873;">
        {{ $greeting }}
    </p>

    <p style="margin:0 0 28px;font-size:15px;line-height:1.65;color:#6e6b7b;">
        {{ $intro }}
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 28px;">
        <tr>
            <td align="center" style="border-radius:8px;background:linear-gradient(135deg,#7367f0 0%,#9e95f5 100%);">
                <a href="{{ $actionUrl }}"
                   style="display:inline-block;padding:14px 32px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                    {{ $actionLabel }}
                </a>
            </td>
        </tr>
    </table>

    @if (! empty($footnote))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
               style="background-color:#f8f8f8;border-radius:10px;">
            <tr>
                <td style="padding:16px 18px;">
                    <p style="margin:0;font-size:13px;line-height:1.65;color:#a8a4b8;text-align:center;">
                        {{ $footnote }}
                    </p>
                </td>
            </tr>
        </table>
    @endif
@endsection
