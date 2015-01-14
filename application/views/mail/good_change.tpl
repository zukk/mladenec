<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

<p>Изменения в товарах:</p>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <th>Название</th>
        <th>Артикул</th>
        <th>Штрихкод</th>
        <th>Причина</th>
        <th>Цена</th>
    </tr>
    {foreach from=$changes item=ch}
        <tr>
            <td  align="left">{$ch['name']}</td>
            <td align="center">{$ch['code']}</td>
            <td align="center">{$ch['barcode']}</td>
            <td align="center">{$ch['reason']}</td>
            <td align="center">{$ch['price']}</td>
        </tr>
    {/foreach}
</table>

			</td>
		</tr>
		</table>
	</td>
</tr>