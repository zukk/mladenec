<div class="order_header">
    <div class="order_step1 active">
        <span>Оформление заказа</span>
        Шаг 1 из 2
    </div>
    <div class="order_step2">
        <span>Подтверждение заказа</span>
        Шаг 2 из 2
    </div>
</div>

<p>Ваш заказ на&nbsp;сумму <strong>{$cart->get_total()|price}</strong></p>

{if not $user}
<div id="login_forma">
    <h1>Для дальнейшей работы необходимо авторизоваться:</h1>
    <form action="/user/login" method="post" class="ajax">
        <div>
            <input name="login" value="" type="text" class="txt" placeholder="Логин / E-mail"/>
        </div>
        <div>
            <input name="password" value="" class="txt" type="password" placeholder="Пароль"/>
        </div>
        <div>
            <label class="label fl"><i class="check"></i><input type="checkbox" name="remember" /> Оставаться в&nbsp;системе</label>
        </div>
        <div>
            <input type="submit" value="Войти" class="butt fl" />
        </div>
        <p>Если Вы забыли Ваш пароль доступа, воспользуйтесь <a href="{Route::url('user_forgot')}">сервисом восстановления пароля</a></p>
    </form>
</div>
{/if}

<form action="/personal/order_data.php" method="post" class="cb cols ajax" id="order_data">
<div id="order_user">
    {if not $user}
        <h2>Если Вы ещё не регистрировались на нашем сайте, заполните Ваши контактные данные:</h2>
    {else}
        <h2>Контактные данные</h2>
    {/if}

    <div class="half">
        <label class="l" for="last_name">Фамилия:</label> <input id="last_name" name="last_name" value="{$user->last_name}" class="txt" />
        <label class="l" for="name">Имя:<sup>*</sup></label> <input id="name" name="name" value="{$user->name}" class="txt" />
        <label class="l" for="name">Отчество:</label> <input name="second_name" value="{$user->second_name}" class="txt" />
    </div>

    <div class="half">
        <label class="l" for="phone">Телефон:<sup>*</sup></label> <input id="phone" name="phone" value="{$user->phone}" class="txt" />
        <label class="l" for="phone2">Доп.телефон:</label> <input id="phone2" name="phone2" value="{$user->phone2}" class="txt" />
        <label class="l" for="mobile_phone">Телефон для СМС:</label> <input id="mobile_phone" name="mobile_phone" value="{if $user->order_notify < 2}{if Txt::phone_is_mobile($user->phone)}{Txt::phone_clear($user->phone)}{elseif Txt::phone_is_mobile($user->phone2)}{Txt::phone_clear($user->phone2)}{/if}{/if}" class="txt" />
        <label class="l" for="email">E-mail:<sup>*</sup></label> <input id="email" name="email" value="{$user->email}" class="txt" />
    </div>
    {if not empty($user)}
    <label class="label cl"><i class="check"></i>
        <input type="checkbox" name="save_user" value="1" id="save_user"{if ! isset($smarty.get.save_user) OR $smarty.get.save_user} checked="checked"{/if} />
        Сохранить данные в&nbsp;личном кабинете 
    </label>
    <abbr abbr="Не&nbsp;устанавливайте эту галочку, если вы&nbsp;сообщаете эти параметры только для&nbsp;данного заказа и&nbsp;не&nbsp;хотите сохранять их&nbsp;изменения в&nbsp;&quot;Личном кабинете&quot;.">Что это?</abbr>
    {/if}
    {if not $is_kiosk|default:false}
        <script type="text/javascript">
            {literal}
                $(document).ready(function() {
                    $('#order_user input[name=phone], #order_user input[name=phone2],#order_user input[name=mobile_phone]').mask('+7(999)999-99-99');
                });
            {/literal}
        </script>
    {/if}
    
</div>

<div id="order_delivery" class="cb">
    <h2 class="mt">Способ доставки</h2>

    <div>
        <label class="label"><i class="radio"></i><input type="radio" name="delivery_type" value="{Model_Order::SHIP_COURIER}" id="dt{Model_Order::SHIP_COURIER}"/> Доставка нашей курьерской службой (только Москва и МО)</label>

        {if $cart->get_total() lt 2500}
            <label class="label"> Доставка транспортной компанией возможна только для заказов <strong>от&nbsp;2500&nbsp;руб</strong> (кроме Москвы и&nbsp;МО)</label>
        {else}
            <label class="label"><i class="radio"></i><input type="radio" name="delivery_type" value="{Model_Order::SHIP_SERVICE}" id="dt{Model_Order::SHIP_SERVICE}" /> Доставка транспортной компанией (кроме Москвы и&nbsp;МО)</label>
        {/if}

        {*<label class="label"><i class="radio"></i><input type="radio" name="delivery_type" value="{Model_Order::SHIP_SELF}" id="dt{Model_Order::SHIP_SELF}" /> Самовывоз</label>*}
    </div>
</div>

<div id="delivery"><i class="load"></i>Загружается меню доставки&hellip;</div>

<div class="cl mt"></div>

</form>

<script type="text/javascript">
var falled = false;
{literal}
$(document).ready(function() {
    $('input[name="delivery_type"]').change(function() {
        var type = $(this).val();
		if (falled) {
            var s = $('#street').val(), h = $('#house').val();
        }

        $('#delivery').html('<i class="load"></i>').load('/delivery/' + type, function() {

            $('#delivery input:checkbox').checkbox();
            $('#delivery input:radio').radio();

	        if (falled) {
                $('input[name="address_id"]').last().click(); // check last option
                $('#city').val(falled); $('#street').val(s); $('#house').val(h);
                fillReg(falled);
                falled = false;
            }

            {/literal}
            {if not $is_iframed}
            {literal}
                var scrollTo = $('#login_forma').length ? $("#login_forma") : $("#delivery");
                $('html, body').animate({scrollTop: scrollTo.offset().top - 40}, 'fast');
            {/literal}
            {/if}
            {literal}
        });
    });
{/literal}

    $('#dt{$dt}').click(); {* choose default delivery type *}
    $('#dt{$dt}').change(); 

{literal}

});
{/literal}
</script>
