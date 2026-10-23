@props([
    'code',
    'label' => 'Verification code',
])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td align="center" style="background-color:#F3FAFE;border:1px solid #CBE8F8;border-radius:6px;padding:20px 16px;">
            <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:12px;line-height:16px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#5B7080;padding-bottom:8px;">{{ $label }}</div>
            <div class="sss-code" style="font-family:SFMono-Regular,Menlo,Consolas,'Liberation Mono',monospace;font-size:34px;line-height:44px;font-weight:700;letter-spacing:8px;color:#083049;white-space:nowrap;-webkit-user-select:all;user-select:all;">{{ $code }}</div>
        </td>
    </tr>
</table>
