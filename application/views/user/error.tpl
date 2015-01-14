<h2>Нашли ошибку?</h2>

<p>
    Если Вы&nbsp;считаете, что что-то на&nbsp;сайте работает неправильно,<br />
    пожалуйста, сообщите нам о&nbsp;возникших проблемах.<br />
    Эта&nbsp;информация очень поможет нам поскорее их&nbsp;исправить.
</p>

<form action="/user/error" method="post" class="ajax cols small" style="width:420px">

    <label for="problem" class="l" style="white-space:nowrap;">Подробно опишите проблему:</label>
    <textarea class="txt" name="problem" id="problem" style="height:200px; width:380px;"></textarea>

    {if not $user}
        <label for="email" class="l" >Email для связи:</label>
        <input class="txt" name="email" id="email" />
    {/if}

    <div class="cl">
        {if not $user}
            <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
            <div class="fl">
                <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
                <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
            </div>
        {/if}
        <input name="save_error" value="Отправить" type="submit" class="butt small" />
    </div>
</form>
