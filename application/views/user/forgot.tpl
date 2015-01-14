<h1>Восстановление пароля</h1>

<form action="" method="post" id="forgot_form"  class="ajax cols">
    <div class="half">
        <label class="l" for="email">E-mail</label><input id="email" name="email" class="txt" value="" />

        <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
        <div class="fl">
            <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
            <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
        </div>
    </div>

    <input name="send" value="Восстановить пароль" type="submit" class="butt" />

</form>