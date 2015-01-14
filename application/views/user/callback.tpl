<h2>Заказать звонок</h2>

{if empty($sent)}
<form action="/callback" method="post" id="callback" class="ajax cols small">

    <div>
        <label for="cname" class="l">Ваше имя <sup>*</sup></label>
        <input type="text" id="cname" name="name" value="{$user->name|default:''}" class="txt" />
    </div>
    <div>
        <label for="cphone" class="l">Ваш телефон <sup>*</sup></label>
        <input type="text" id="cphone" name="phone" value="{$user->phone|default:''}" class="txt" />
    </div>

    <div class="cl">
        {if not $user}
            <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
            <div class="fl">
                <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
                <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
            </div>
        {/if}
        <input name="save_callback" value="Отправить" type="submit" class="butt small" />
    </div>
</form>
<script type="text/javascript">
{literal}
$(document).ready(function() {
    $('#callback input[name=phone]').mask('+7(999)999-99-99');
});
{/literal}
</script>

    {else}

<div class="ok">
    Заказ звонка принят
</div>

{/if}