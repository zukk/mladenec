<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

<table cellpadding="0" cellspacing="0">
<tr>
    <td align="left" style="border-bottom: 1px solid #0099cc;">
        <br><h3>Здравствуйте, {$theme->user_name}!<br></h3>
        <p>Вы оставили вопрос в&nbsp;книге отзывов интернет-магазина <a href="{$site}">Младенец.РУ</a>:</p>
        <p><strong>&laquo;{$comment->text|nl2br}&raquo;</strong></p>
    </td>
</tr>
<tr>
    <td align="left">
		<p>Отделом &laquo;{$i->get_answer_by()}&raquo; был дан ответ:</p>
		<p><strong>&laquo;{$i->answer}<br>С уважением, {$i->get_answer_by()}.&raquo;</strong></p>
    </td>
</tr>
<tr>
    <td>
        {if $theme->active and $comment->active}
			<p><a href='{Mail::site()}{Route::url('comment', ['id' => $theme->id])}?hash={$hash}'><img src='{Mail::site()}/i/mail/answer-button.png' alt='Ответить' /></a></p>
            <p>Вы&nbsp;можете увидеть свой отзыв по&nbsp;адресу:
            {capture assign=href}{$site}{Route::url('comment', ['id' => $theme->id])}?hash={$hash}{/capture}
            <a href="{$href}">{$href}</a></p>
        {/if}
		Благодарим вас за&nbsp;отзыв. Спасибо!<br><br>
    </td>
</tr>
</table>

			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>
