<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#F4F4F5;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F4F4F5;padding:32px 16px;">
<tr>
<td align="center">
    <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;">

        {{-- Header --}}
        <tr>
            <td style="background-color:#0A0A0A;border-radius:16px 16px 0 0;padding:28px 32px;text-align:center;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td align="center">
                            <span style="font-size:22px;font-weight:900;color:#FFFFFF;letter-spacing:-0.5px;">{{ $store->name() }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-top:6px;">
                            <span style="font-size:12px;color:#8c6b35;text-transform:uppercase;letter-spacing:2px;">Curated Womenswear</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Body --}}
        <tr>
            <td style="background-color:#FFFFFF;padding:0;">
                {{ $slot }}
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="background-color:#0A0A0A;border-radius:0 0 16px 16px;padding:24px 32px;text-align:center;">
                <p style="margin:0 0 8px;font-size:13px;color:#A1A1AA;line-height:1.6;">
                    Need help? Contact us anytime
                </p>
                <p style="margin:0 0 16px;font-size:13px;color:#FFFFFF;line-height:1.6;">
                    <a href="mailto:{{ $store->email() }}" style="color:#E31E24;text-decoration:none;">{{ $store->email() }}</a>
                    &nbsp;·&nbsp; {{ $store->displayPhone() }}
                </p>
                <p style="margin:0;font-size:11px;color:#71717A;line-height:1.5;">
                    &copy; {{ date('Y') }} {{ $store->name() }}. All rights reserved.<br>
                    <a href="{{ url('/') }}" style="color:#71717A;text-decoration:underline;">Visit our store</a>
                </p>
            </td>
        </tr>

    </table>
</td>
</tr>
</table>
</body>
</html>
