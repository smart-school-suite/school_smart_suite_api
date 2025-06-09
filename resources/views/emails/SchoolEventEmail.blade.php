<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notification' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base styles */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            color: #333;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%;
        }

        td {
            padding: 0;
            vertical-align: top;
        }

        img {
            border: 0;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
            height: auto;
        }

        a {
            text-decoration: none;
            color: #1a73e8; /* Google Blue for links */
        }

        /* Container styles */
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Header styles */
        .header {
            background-color: #202124; /* Google Dark Grey */
            padding: 24px 20px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #ffffff;
            font-weight: 600;
        }

        /* Body content styles */
        .content-body {
            padding: 30px 20px;
            line-height: 1.6;
            color: #4a4a4a;
        }

        .content-body h2 {
            margin-top: 0;
            font-size: 24px;
            color: #202124;
            font-weight: 500;
        }

        .content-body p {
            margin-bottom: 15px;
            font-size: 16px;
        }

        /* Footer styles */
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #70757a;
            background-color: #e8eaed; /* Lighter grey */
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .footer p {
            margin: 0;
        }

        /* Responsive adjustments */
        @media only screen and (max-width: 620px) {
            .container {
                width: 100% !important;
                border-radius: 0 !important;
            }
            .header, .content-body, .footer {
                padding: 20px !important;
            }
            .header h1 {
                font-size: 24px !important;
            }
            .content-body h2 {
                font-size: 20px !important;
            }
            .content-body p {
                font-size: 15px !important;
            }
        }
    </style>
</head>
<body>
    <center class="wrapper" style="width: 100%; table-layout: fixed; background-color: #f4f7f6; padding-top: 20px; padding-bottom: 20px;">
        <table class="container" role="presentation" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
            <tr>
                <td align="center" class="header" style="background-color: #202124; padding: 24px 20px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                    <h1 style="margin: 0; font-size: 28px; color: #ffffff; font-weight: 600;">{{ $title  }}</h1>
                </td>
            </tr>
            <tr>
                <td class="content-body" style="padding: 30px 20px; line-height: 1.6; color: #4a4a4a;">
                    <h2 style="margin-top: 0; font-size: 24px; color: #202124; font-weight: 500;">Hello there!</h2>
                    <p style="margin-bottom: 15px; font-size: 16px;">
                        {{ $description }}
                    </p>
                    <p style="margin-bottom: 15px; font-size: 16px;">
                        Thank you for your attention.
                    </p>
                </td>
            </tr>
            <tr>
                <td class="footer" style="padding: 20px; text-align: center; font-size: 12px; color: #70757a; background-color: #e8eaed; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <p style="margin: 0;">&copy; {{ date('Y') }} Your Company Name. All rights reserved.</p>
                    <p style="margin: 5px 0 0;">
                        <a href="#" style="text-decoration: none; color: #1a73e8;">Unsubscribe</a> |
                        <a href="#" style="text-decoration: none; color: #1a73e8;">Privacy Policy</a>
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
