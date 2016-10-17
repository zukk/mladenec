<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

            <p>Пользователь, {$r->name} оставил претензию №{$r->id}{if not empty($r->order_num)} (номер заказа: {$r->order_num}){/if}:<br /><br />
                {$r->text|nl2br}
            </p>

			</td>
		</tr>
		</table>
	</td>
</tr>