<h2>Уведомить о поставке</h2>

{if empty($sent)}
<form action="/product/warn/{$good->id}" method="post" id="warn" class="ajax cols small">

    <div class="half">
        {capture assign=name}{$good->group_name|escape:'html'} {$good->name|escape:'html'}{/capture}
        <img src="{$good->prop->get_img(255)}" alt="{$name}" style="margin-bottom: 5px " />
    </div>
    <div class="half">
        <h3>{$name}</h3>
        <br />

        {*<label for="q" class="l">Количество <sup>*</sup></label>
        <input type="text" id="q" name="qty" class="txt short" value="1" />*}

        <input type="hidden" name="qty" value="1" />

        <label for="email" class="l">E-mail <sup>*</sup></label>
        <input type="text" id="email" name="email" class="txt" value="{$user->email|default:''}" style="height: 26px;" />

        {if not $user}
            <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
            <div class="fl">
                <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
                <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
            </div>
        {/if}
		
	    <input name="send" value="Отправить" type="submit" class="butt" style="margin: 0;" />
    </div>

</form>

    {else}

<div class="ok">
    Спасибо!<br />
    Уведомление о&nbsp;поставке товара <strong>{$good->group_name} {$good->name}</strong><br />
    будет выслано на&nbsp;почту <strong>{$smarty.post.email}</strong>.
</div>

{/if}