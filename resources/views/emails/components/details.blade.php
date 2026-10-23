@props([
    'rows' => [],
])
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;border-collapse:collapse;border-top:1px solid #E3E9EE;">
    @foreach ($rows as $label => $value)
        @continue(blank($value))
        <tr>
            <th scope="row" align="left" valign="top" style="width:40%;padding:12px 16px 12px 0;border-bottom:1px solid #E3E9EE;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:22px;font-weight:400;color:#5B7080;text-align:left;">{{ $label }}</th>
            <td align="left" valign="top" style="padding:12px 0;border-bottom:1px solid #E3E9EE;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:15px;line-height:22px;font-weight:600;color:#083049;word-break:break-word;">{{ $value }}</td>
        </tr>
    @endforeach
</table>
