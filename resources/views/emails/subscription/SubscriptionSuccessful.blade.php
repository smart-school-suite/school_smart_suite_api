<x-email::layout
    title="Your Smart School Suite subscription is confirmed"
    :preheader="'Your subscription for ' . $schoolName . ' is confirmed.'">

    <h1 class="sss-h1" style="margin:0 0 16px 0;font-size:24px;line-height:32px;font-weight:700;color:#083049;">
        Subscription confirmed
    </h1>

    <p style="margin:0 0 8px 0;">
        {{ filled($recipientName) ? 'Hello ' . $recipientName . ',' : 'Hello,' }}
    </p>

    <p style="margin:0;">
        Thank you. We have received your payment and your Smart School Suite subscription
        is confirmed. Please keep this email for your records.
    </p>

    <x-email::details :rows="[
        'School'                => $schoolName,
        'Subscription plan'     => $planName,
        'Amount paid'           => $currency . ' ' . $amount,
        'Start date'            => $startDate,
        'End date'              => $endDate,
        'Payment reference'     => $paymentReference,
        'Subscription status'   => $status,
    ]" />

    <x-email::notice title="Questions about this payment?">
        If any detail above looks incorrect, or you don't recognise this payment,
        contact your school administrator or the Smart School Suite support team.
    </x-email::notice>

</x-email::layout>
