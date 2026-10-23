<x-email::layout
    title="Your Smart School Suite verification code"
    :preheader="'Use this code to continue. It expires in ' . $expiresInMinutes . ' minutes.'">

    <h1 class="sss-h1" style="margin:0 0 16px 0;font-size:24px;line-height:32px;font-weight:700;color:#083049;">
        Your verification code
    </h1>

    <p style="margin:0 0 8px 0;">
        {{ filled($recipientName) ? 'Hello ' . $recipientName . ',' : 'Hello,' }}
    </p>

    <p style="margin:0;">
        We received a request to verify your identity on Smart School Suite.
        Enter the code below on the verification screen to continue.
    </p>

    <x-email::code-block :code="$otp" />

    <p style="margin:0;">
        This code expires in <strong>{{ $expiresInMinutes }} minutes</strong>.
        Enter it in the Smart School Suite screen where you made this request.
    </p>

    <x-email::notice title="Security notice">
        Never share this code with anyone. Smart School Suite will never ask you for it.
        If you didn't request this code, you can safely ignore this email. If you keep
        receiving codes you didn't request, let your school administrator know.
    </x-email::notice>

</x-email::layout>
