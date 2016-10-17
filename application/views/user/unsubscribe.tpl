<h1>Отписаться от рассылки</h1>

{if ! empty($done)}
	<p>Почтовый адрес <strong>{$smarty.get.mail}</strong> был отписан от&nbsp;всех рассылок</p>

{else}

	{if ! empty($subscribed)}
		<p>Расскажите нам, почему Вы отписываетесь<!--Вы&nbsp;уверены, что не&nbsp;хотите получать новости и&nbsp;выгодные предложения по&nbsp;<nobr>e-mail</nobr-->?</p>
		<form action="" method="post">

            <textarea class="txt" name="reason" cols="50" /></textarea>
			<input type="submit" name="do" value="Да, отписаться" class="small butt fl" />
			<a href="/" class="no fl ml20" style="line-height:32px;">Нет</a>
		</form>
	{else}
		<p>Почтовый адрес <strong>{$smarty.get.mail}</strong> уже был отписан от&nbsp;всех рассылок</p>
	{/if}
{/if}


<p class="cb mt">
	<br />
	Вы&nbsp;всегда можете подписаться на&nbsp;рассылку об&nbsp;акциях,
	скидках, подарках и&nbsp;конкурсах в&nbsp;<a href="{Route::url('user')}">Личном кабинете</a>
</p>