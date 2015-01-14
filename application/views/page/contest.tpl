{if $sent|default:0}
    <div class="cl">
        <h2>Спасибо</h2>
        <p>Ваше сообщение принято.</p>
    </div>
{else}
    <form action="" method="post" class="cols" id="partner_form" enctype="multipart/form-data">

        <label class="l" for="text">Сообщение<sup>*</sup></label>
        <textarea id="text" name="text" class="wtxt {if ! empty($errors['text'])}error" error="{$errors['text']}{/if}" rows="10">{$smarty.post.text|default:''}</textarea>
        <p>Не более 2000 символов.</p>

        {if not $user}
            <label class="l" for="name">Имя<sup>*</sup></label>
            <input id="name" name="name" class="txt {if ! empty($errors.name)}error" error="{$errors.name}{/if}" value="{$smarty.post.name|default:''}" />

            <label class="l" for="email">Email<sup>*</sup></label>
            <input id="email" name="email" class="txt {if ! empty($errors.email)}error" error="{$errors.email}{/if}" value="{$smarty.post.email|default:''}" />

            <label for="captcha" class="l"><img src="/captcha" alt="" /></label>

            <div class="fl">
                <label>Введите цифры с&nbsp;картинки:&nbsp;<sup>*</sup></label><br />
                <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt {if ! empty($errors.captcha)}error" error="{$errors.captcha}{/if}"/>
            </div>
        {else}
            {if ! empty($errors.email)}
                <p>Укажите ваш email в <a href="/account">профиле</a></p>
            {/if}
        {/if}
        {if empty($is_kiosk)}
            <label class="wl" for="price">Приложите файл<sup>*</sup></label>
            <div class="fl"><input type="file" name="price" /><br />
                {if ! empty($errors.price)}<b style="color:red">{$errors.price}</b>{/if}
                Принимаются картинки в формате .jpg. Mаксимальный размер файла &mdash;&nbsp;2.5&nbsp;мб.
            </div>
        {/if}

        <p class="cb"><br /><input type="submit" value="Отправить заявку" class="butt" name="contest"/></p>
    </form>
{/if}