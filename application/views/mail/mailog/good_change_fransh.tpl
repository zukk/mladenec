<p>Изменения закупочных цен:</p>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <th>Дата<br />Время</th>
        <th>Название</th>
        <th>Артикул</th>
        <th>Штрихкод</th>
        <th>Причина</th>
        <th>Было</th>
        <th>Стало</th>
    </tr>
    {foreach from=$logs item=log}
        {$data = $log->get_data()}
        <tr>
            <td align="center">{$log->date}<br />{$log->time}</td>
            <td align="left">{$data['name']|default:'-'}</td>
            <td align="center">{$data['code']|default:'-'}</td>
            <td align="center">{$data['barcode']|default:'-'}</td>
            <td align="center">{$data['reason']|default:'-'}</td>
            <td align="center">{$data['was']|default:'-'}</td>
            <td align="center">{$data['price']|default:'-'}</td>
        </tr>
    {/foreach}
</table>