<h1>Личный кабинет</h1>

<div class="tabs mt">
    <div>
        <a class="t active" href="{Route::url('user')}">Мои данные</a>
        <a class="t" href="{Route::url('order_list')}">Мои заказы</a>
        <a class="t" href="{Route::url('user_address')}">Мои адреса</a>
        <a class="t" href="{Route::url('user_child')}">Мои дети</a>
        <a class="t" href="{Route::url('user_action')}">Мои баллы по акции</a>
        <a class="t" href="{Route::url('user_reviews')}">Мои отзывы</a>
    </div>
    <div class="tab-content active">
        <form action="" method="post" class="ajax cols">
            <div class="half">
                <label class="l" for="email">E-mail<sup>*</sup></label><input id="email" name="email" class="txt" value="{$user->email}" readonly="readonly"/>
                <label class="l" for="name">Имя<sup>*</sup></label><input id="name" name="name" class="txt" value="{$user->name}" />
                <label class="l" for="sname">Отчество</label><input id="sname" name="second_name" class="txt" value="{$user->second_name}" />
                <label class="l" for="lname">Фамилия</label><input id="lname" name="last_name" class="txt" value="{$user->last_name}" />
                <label class="l" for="phone">Телефон<sup>*</sup></label><input id="phone" name="phone" class="txt" value="{$user->phone}" />
                <label class="l" for="phone2">Доп.Телефон</label><input id="phone2" name="phone2" class="txt" value="{$user->phone2}" />
            </div>
            <div class="half">
                <p><a href="/account/password" class="no">Сменить пароль</a></p>
	        <label class="label"><i class="check"></i><input name="sub" type="checkbox" {if $user->sub}checked="checked"{/if} /> Получать новости и&nbsp;выгодные предложения по&nbsp;email и&nbsp;СМС</label>
                {if Txt::phone_is_mobile($user->phone) OR  Txt::phone_is_mobile($user->phone2)}
                    <h3 style="margin-top:12px;">Информировать о статусе заказов:</h3>
                    <label class="label"><i class="radio"></i><input type="radio" name="order_notify" value="0" {if $user->order_notify eq 0}checked="checked"{/if} /> По электронной почте и СМС.</label>
                    {*<label class="label"><i class="radio"></i><input type="radio" name="order_notify" value="1" {if $user->order_notify eq 1}checked="checked"{/if} /> Только СМС.</label>*}
                    <label class="label"><i class="radio"></i><input type="radio" name="order_notify" value="2" {if $user->order_notify eq 2}checked="checked"{/if} /> Только по электронной почте.</label>
                {/if}
            </div>
            <input type="submit" value="Изменить" class="butt"/>
        </form>
    </div>
</div>

<script type="text/javascript">
{literal}
$(document).ready(function() {
    $('#content input[name=phone]').mask('+7(999)999-99-99');
    $('#content input[name=phone2]').mask('+7(999)999-99-99');
   // $('#content input[name=mobile_phone]').mask('+7(999)999-99-99');
});
{/literal}
</script>
