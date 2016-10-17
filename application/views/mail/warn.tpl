<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

<br><h3>Здравствуйте{if ! empty($user->name)}, {$user->name}{/if}!<br></h3>
<p>Вы&nbsp;оставляли заявку на&nbsp;уведомление о&nbsp;поставке товара
	&laquo;<strong>{$g->group_name} {$g->name}</strong>&raquo;
</p>

<p>
    Спешим сообщить вам о&nbsp;наличии данного товара у&nbsp;нас на&nbsp;складах.<br /><br />
    {capture assign=href}{$site}{$g->get_link(0)}{/capture}
    Заказать этот товар можно по&nbsp;ссылке: <a href="{$href}">{$href}</a>
</p>
<br>

			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>