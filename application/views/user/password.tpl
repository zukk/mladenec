<div id="breadcrumb">
    <a href="{Route::url('user')}">Личный кабинет</a> |
</div>


<div id="simple">
    <h1>Смена пароля</h1>

    <form action="{Route::url('user_password')}{if $code}?code={$code}{/if}" method="post" class="ajax cols" autocomplete="off">
        <div class="half">
{if empty($code)}
            <label class="l">Текущий пароль:<sup>*</sup></label>
            <input type="password" name="old_password" class="txt" value="" />
{/if}
            <label class="l" for="new_password"><abbr class="info" abbr="Пароль должен состоять из&nbsp;6&ndash;20
                символов русского и/или латинского алфавитов и/или цифр.<br />
                <em>Совет: не&nbsp;создавайте слишком простых паролей. Комбинируйте буквы и&nbsp;цифры.</em>">Новый</abbr> пароль:
                <sup>*</sup>

            </label>
            <input type="password" id="new_password" name="password" class="txt" value="" />

            <label class="l" for="new_password2">Повторите новый пароль:<sup>*</sup></label>
            <input type="password" id="new_password2" name="password2"  class="txt" value="" />
        </div>

        <input name="reset_password" value="Сменить пароль" type="submit" class="butt" />

    </form>
</div>