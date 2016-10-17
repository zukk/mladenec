<h2>Заказать звонок</h2>

{if empty($sent)}
<form action="{Route::url('callback')}" method="post" id="callback" class="ajax cols small" style="position: relative;">

    <div>
        <label for="cname" class="l">Ваше имя <sup>*</sup></label>
        <input type="text" id="cname" name="name" value="{$user->name|default:''}" class="txt" />
    </div>
    <div>
        <label for="cphone" class="l">Ваш телефон <sup>*</sup></label>
        <input type="tel" id="cphone" name="phone" value="{$user->phone|default:''}" class="txt" />
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
    var cf = $('#callback');
    $(cf, 'input[name=phone]').mask('+7(999)999-99-99');

    var loader = $('<i class="load"></i>');

    $('[name=save_callback]').click(function(){
        var params = $('#callback').serialize()+"&ajax=1&save_callback=Отправить";
        var timeout = setTimeout(function(){
            $('#callback').parent().append(loader);
            $(loader).css({
                display: 'block',
                position: 'absolute',
                top: '50%',
                left: '50%',
                'margin-top': '-12px',
                'margin-left': '-12px',
                padding: '10px',
                'border-radius': '5px',
                'background-color': 'white'
            });
        }, 100);

        $.post(
            $('#callback').attr('action'),
            params,
            function(data) {

                window.dataLayer = window.dataLayer || [];

                clearTimeout(timeout);
                $(loader).remove();

                if( data.html ){

                    dataLayer.push({ 'event': 'obrzvonok' });
                    $('#callback').parent().html(data.html);
                    $.fancybox.update();
                }

                if( data.error ){
                    alert('Проверьте правильность заполнения полей');
                }
            }, 'json'
        );
        return false;
    });
});
{/literal}
</script>

    {else}

<div class="ok">
    Заказ звонка принят
</div>

{/if}