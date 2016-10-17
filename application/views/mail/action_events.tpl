<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

<p>Изменения в акциях:</p>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <th>ID</th>
        <th>Событие<br />Причина</th>
        <th>Истекает</th>
        <th>Название</th>
        <th>Входящая ссылка</th>
        <th>В админке<br />На сайте</th>
        <th>Комментарий к баннеру</th>
    </tr>
    {foreach from=$reports item=r}
        {$action = $r['action']}
        <tr>
            <td align="center">{$action->id}</td>
            <td align="center">
                {if $r['event'] eq 'on'}
                    <font color="green">ВКЛючена</font>
                {elseif $r['event'] eq 'off'}
                    <font color="red">ОТКЛючена</font>
                {elseif $r['event'] eq 'allow'}
                    <font color="red">Разрешена к включению</font>
                {elseif $r['event'] eq 'disallow'}
                    <font color="red">ОТКЛючена, остановлена</font>
                {elseif $r['event'] eq 'incoming_link_flag_off'}
                    <font color="red">Снят флаг входящей ссылки</font>
                {elseif $r['event'] eq 'presents_instock'}
                    <font color="green">Подарки появились на складе</font>
                {elseif $r['event'] eq 'presents_off'}
                    <font color="red">Закончились подарки</font>
                {/if}
                <br />{$r['msg']|default:'no message'}
            </td>
            <td align="center">{$action->to}</td>
            <td align="left">{$action->name}</td>
            <td align="left">{if $action->incoming_link|default:0}да!{else}нет{/if}</td>
            <td align="center">
                <a href="{Mail::site()}{Route::url('admin_edit', ['model'=>'action','id'=>$action->id])}" target="_blank">В админке</a><br />
                <a href="{Mail::site()}{Route::url('action', ['id'=>$action->id])}" target="_blank">На сайте</a>
            </td>
            <td align="center">{$action->link_comment}</td>
        </tr>
    {/foreach}
</table>

			</td>
		</tr>
		</table>
	</td>
</tr>
