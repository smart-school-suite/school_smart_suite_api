@props([
    'title' => null,
])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0 0 0;">
    <tr>
        <td style="background-color:#F9F9F9;border-left:3px solid #0EA7E9;padding:14px 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:22px;color:#083049;">
            @if ($title)
                <strong style="display:block;margin-bottom:2px;">{{ $title }}</strong>
            @endif
            {{ $slot }}
        </td>
    </tr>
</table>
