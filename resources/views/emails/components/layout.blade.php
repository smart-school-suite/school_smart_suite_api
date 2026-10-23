@props([
    'title' => config('sss.email.product_name'),
    'preheader' => null,
    'footerNote' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $title }}</title>
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0; mso-table-rspace: 0; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        @media only screen and (max-width: 620px) {
            .sss-outer { padding: 16px 8px !important; }
            .sss-px { padding-left: 20px !important; padding-right: 20px !important; }
            .sss-h1 { font-size: 22px !important; line-height: 30px !important; }
            .sss-code { font-size: 28px !important; letter-spacing: 5px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#F9F9F9;">
    @if ($preheader)
        <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#F9F9F9;opacity:0;">{{ $preheader }}</div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F9F9F9;">
        <tr>
            <td align="center" class="sss-outer" style="padding:32px 16px;">
                <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                    <tr>
                        <td>
                            {{-- Card --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF;border:1px solid #E3E9EE;border-radius:8px;border-collapse:separate;overflow:hidden;">
                                <tr>
                                    <td style="height:4px;line-height:4px;font-size:0;background-color:#0EA7E9;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="sss-px" style="padding:32px 40px 8px 40px;">
                                        <img src="{{ config('sss.email.logo_url') }}"
                                             alt="{{ config('sss.email.product_name') }}"
                                             width="{{ config('sss.email.logo_width') }}"
                                             style="display:block;width:{{ config('sss.email.logo_width') }}px;max-width:100%;height:auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:700;color:#083049;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="sss-px" style="padding:16px 40px 40px 40px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:16px;line-height:26px;color:#083049;">
                                        {{ $slot }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Footer --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" class="sss-px" style="padding:24px 24px 0 24px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:20px;color:#5B7080;">
                                        <strong style="color:#083049;">{{ config('sss.email.product_name') }}</strong><br>
                                        @if ($footerNote)
                                            {{ $footerNote }}<br>
                                        @endif
                                        @if (config('sss.email.support_email'))
                                            Need help? <a href="mailto:{{ config('sss.email.support_email') }}" style="color:#0877A8;text-decoration:underline;">{{ config('sss.email.support_email') }}</a><br>
                                        @endif
                                        &copy; {{ now()->year }} {{ config('sss.email.product_name') }}. All rights reserved.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
