@php
    // Shared single-locale "Deforestory Dispatch" email layout. Used by both
    // deforestory-update (case-based) and deforestory-card (card-based) emails
    // so the two stay visually identical — one design, can't drift.
    //
    // Expected vars (all pre-localized by the caller for the subscriber's locale):
    // $brand, $subline, $tag, $label, $title, $description, $imageUrl (nullable),
    // $dateLabel, $dateText, $buttonLabel, $buttonUrl, $reason, $unsubscribeLabel,
    // $unsubscribeUrl, $footWordmark, $lang.
    $desc = strip_tags((string) $description);
@endphp
<!doctype html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $brand }} · {{ $subline }}</title>
    <style>
        @media only screen and (max-width: 600px) {
            .wrap { width: 100% !important; }
            .pad { padding: 24px 22px !important; }
            .h1 { font-size: 24px !important; line-height: 1.3 !important; }
            .hero { width: 100% !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#eef1ee;-webkit-text-size-adjust:100%;font-family:Arial,Helvetica,sans-serif;color:#2a322e">

    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#eef1ee">
        {{ $title }}
    </div>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef1ee;width:100%">
        <tr>
            <td align="center" style="padding:28px 14px">

                <table role="presentation" class="wrap" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px;max-width:600px;background:#ffffff;border:1px solid #e2e6e3">

                    {{-- alert rule --}}
                    <tr>
                        <td style="height:3px;line-height:3px;font-size:0;background:#DC2626">&nbsp;</td>
                    </tr>

                    {{-- dispatch header --}}
                    <tr>
                        <td style="background:#2B5343">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:18px 28px 16px">
                                        <span style="color:#ffffff;font-size:16px;font-weight:700;letter-spacing:.18em;font-family:Arial,Helvetica,sans-serif">{{ strtoupper($brand) }}<span style="color:#DC2626">.</span></span><br>
                                        <span style="color:#9fb8ad;font-size:10px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;font-family:Arial,Helvetica,sans-serif">{{ $subline }}</span>
                                    </td>
                                    <td align="right" valign="middle" style="padding:18px 28px 16px">
                                        <span style="display:inline-block;background:#DC2626;color:#ffffff;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;padding:5px 11px;font-family:Arial,Helvetica,sans-serif">{{ $tag }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- hero --}}
                    @if (! empty($imageUrl))
                    <tr>
                        <td style="line-height:0;font-size:0">
                            <img src="{{ $imageUrl }}" alt="{{ $title }}" width="600" class="hero" style="display:block;width:100%;max-width:600px;height:auto;border:0">
                        </td>
                    </tr>
                    @endif

                    {{-- body --}}
                    <tr>
                        <td class="pad" style="padding:36px 40px 14px">
                            <p style="margin:0 0 12px;color:#2B5343;font-size:10px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;font-family:'Courier New',Courier,monospace">{{ $label }}</p>
                            <h1 class="h1" style="margin:0 0 16px;font-family:Georgia,'Times New Roman',Times,serif;font-size:26px;line-height:1.28;color:#16201d;font-weight:700">{{ $title }}</h1>
                            @if (! empty($desc))
                            <p style="margin:0 0 20px;font-family:Georgia,'Times New Roman',Times,serif;font-size:15px;line-height:1.65;color:#3d4a44">{{ $desc }}</p>
                            @endif
                            <p style="margin:0 0 24px;font-family:'Courier New',Courier,monospace;font-size:11px;letter-spacing:.04em;color:#2B5343">
                                <span style="color:#8a978f">{{ $dateLabel }}</span>&nbsp;&nbsp;{{ $dateText }}
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0">
                                <tr>
                                    <td bgcolor="#2B5343" style="border-radius:2px">
                                        <a href="{{ $buttonUrl }}" style="display:inline-block;padding:13px 26px;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;text-decoration:none">{{ $buttonLabel }}&nbsp;&nbsp;&rarr;</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- footer --}}
                    <tr>
                        <td style="background:#2B5343;padding:26px 40px 28px">
                            <p style="margin:0 0 16px;color:#bcd0c8;font-family:Georgia,'Times New Roman',Times,serif;font-size:13px;line-height:1.55">{{ $reason }}</p>
                            <p style="margin:0 0 14px">
                                <a href="{{ $unsubscribeUrl }}" style="color:#bcd0c8;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;letter-spacing:.04em;text-decoration:underline">{{ $unsubscribeLabel }}</a>
                            </p>
                            <p style="margin:0;color:#5d706a;font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;font-family:Arial,Helvetica,sans-serif">{{ $footWordmark }}</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>