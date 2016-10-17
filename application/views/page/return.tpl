{if ! empty($p)}
	<h2>Ваша претензия принята</h2>
	<p>Претензии присвоен номер №{$p->id}</p>

{else}

<form action="" method="post" class="cols" id="return_form" enctype="multipart/form-data">
    <label class="l" for="name">Имя<sup>*</sup></label>
    <input id="name" name="name" class="txt {if ! empty($errors.name)}error" error="{$errors.name}{/if}" value="{$smarty.post.name|default:$user->name|default:''}" />

    <label class="l" for="email">Email<sup>*</sup></label>
    <input id="email" name="email" class="txt {if ! empty($errors.email)}error" error="{$errors.email}{/if}" value="{$smarty.post.email|default:$user->email|default:''}" />
    
    <label class="l" for="order-num">Номер заказа</label>
    <input id="order-num" name="order_num" class="txt" value="{$smarty.post.order_num|default:''}" />     

    <label class="l" for="text">Сообщение<sup>*</sup></label>
    <textarea id="text" name="text" class="txt {if ! empty($errors.txt)}error" error="{$errors.text}{/if}" rows="10">{$smarty.post.text|default:''}</textarea>

    <label class="l" for="img">Прикрепить фото<br /> <small>до 10Мб</small></label>
	<div class="fl"><input type="file" name="img" /></div>

    {if not $user}
        <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
        <div class="fl">
            <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
            <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt {if ! empty($errors.captcha)}error" error="{$errors.captcha}{/if}"/>
        </div>
    {/if}
    <input type="submit" value="Отправить претензию" class="butt" name="return" />
</form>
{/if}
