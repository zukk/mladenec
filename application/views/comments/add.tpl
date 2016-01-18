{if $theme->id}
<h2>Ответить</h2>
{else}
<h2>Оставить отзыв</h2>
{/if}

{if not empty($errors)}
<ul class="errors">
    {foreach from=$errors key=name item=e}
     <li>{$e}</li>
    {/foreach}
</ul>
<script type="text/javascript">
    {foreach from=$errors key=name item=e}
    $('#comment #{$name}').addClass('error');
    {/foreach}
</script>
{/if}

{if empty($sent)}
<form action="/about/review/add" method="post" id="comment_form" class="ajax cols small">

	<input type="hidden" name="theme_id" value="{$theme->id}" />
	
	{if not $theme->id}
    <label class="l" for="to">Кому <sup>*</sup></label>
    <select name="to" id="to" class="small">
        <option value="">(выбрать)</option>
        {html_options options=$i->get_to() selected=$i->to}
    </select>
	{/if}

    {if $user}
        <input type="hidden" name="user_id" value="{$user->id}" />
        <input type="hidden" name="user_name" value="{$user->name}" />
        <input type="hidden" name="email" value="{$user->email}" />
        <input type="hidden" name="phone" value="{$user->phone}" />
    {else}
		{if not $theme->id}
        <label for="user_name" class="l">Ваше ФИО <sup>*</sup></label>
        <input type="text" id="user_name" name="user_name" value="" class="txt"/>
        <label for="email" class="l">Ваш Email <sup>*</sup></label>
        <input type="text" id="email" name="email" value="" class="txt"/>
        <label for="user_name" class="l">Ваш Телефон <sup>*</sup></label>
        <input type="tel" id="phone" name="phone" value="" class="txt"/>
		{/if}
    {/if}

	{if not $theme->id}
    <label for="check" class="l">№&nbsp;Заказа</label>
    <input type="text" id="check" name="check" value="" class="txt" />
    <label for="name" class="l">Тема сообщения <sup>*</sup></label>
    <input type="text" id="name" name="name" value="" class="txt" />
	{/if}
    <label for="text" class="l">Cообщение <sup>*</sup></label>
    <textarea id="text" name="text" cols="40" rows="4" class="txt"></textarea>

    {if not $user}
        <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
        <div class="fl">
            <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
            <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
        </div>
    {/if}

    <input name="send" value="Отправить" type="submit" class="butt small" onclick="return confirm('Готовы отправить отзыв?')"/>

</form>

{else}

    <div class="ok" id="comment_form""><br />
        Ваше сообщение отправлено, в&nbsp;ближайшее время будет рассмотрено и&nbsp;ответ будет отправлен вам на&nbsp;<nobr>e-mail</nobr>. В&nbsp;случае, если вы&nbsp;оставили жалобу на&nbsp;работу нашего магазина, нам необходимо время для проверки и&nbsp;исправления указаных вами фактов. Просим вас с&nbsp;пониманием отнестись к&nbsp;тому, что срок ответа в&nbsp;таком случае составит от&nbsp;одного до&nbsp;трех рабочих дней.
    </div>

{/if}