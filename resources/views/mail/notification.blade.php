@extends('mail.layout')

@section('mail-content')
    @php($palette = ($tones ?? [])[$tone ?? 'primary'] ?? ['accent' => '#7367f0', 'soft' => '#f3f1fe', 'border' => '#dcd6ff'])

    <p style="margin:0 0 8px;font-size:15px;font-weight:600;color:#5e5873;">
        {{ $greeting }}
    </p>

    <p style="margin:0 0 24px;font-size:15px;line-height:1.65;color:#6e6b7b;">
        {{ $intro }}
    </p>

    @if (! empty($details))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
               style="margin-bottom:24px;background-color:{{ $palette['soft'] }};border:1px solid {{ $palette['border'] }};border-radius:12px;">
            @foreach ($details as $row)
                <tr>
                    <td style="padding:14px 18px;@if (! $loop->last) border-bottom:1px solid {{ $palette['border'] }}; @endif">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="width:38%;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#a8a4b8;vertical-align:top;">
                                    {{ $row['label'] }}
                                </td>
                                <td style="font-size:14px;font-weight:600;color:#5e5873;line-height:1.5;vertical-align:top;">
                                    {{ $row['value'] }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @if (! empty($actionUrl) && ! empty($actionLabel))
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 24px;">
            <tr>
                <td align="center" style="border-radius:8px;background:linear-gradient(135deg,#7367f0 0%,#9e95f5 100%);">
                    <a href="{{ $actionUrl }}"
                       style="display:inline-block;padding:13px 28px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                        {{ $actionLabel }}
                    </a>
                </td>
            </tr>
        </table>
    @endif

    @if (! empty($footnote))
        <p style="margin:0;font-size:13px;line-height:1.6;color:#a8a4b8;text-align:center;">
            {{ $footnote }}
        </p>
    @endif
@endsection
