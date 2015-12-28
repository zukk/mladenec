<script>
function add_child() {
    var f = $('.nc').eq(0);
    if (f.hasClass('hide')) {
        f.removeClass('hide');
    } else {
        var li = f.clone();

	    li.find('input').each(function() {
	        var new_name = $(this).attr('name').replace(0, $('.nc').length);
	        $(this).val('').attr('name', new_name);
	        if ($(this).prop('type') == 'radio') {
		        $(this).radio();
	        }
        });
        f.parent().append(li);
    }
}

$(document).on('click','#pregnant-check', function(){
    if( ! $(this).find('input').prop('checked')){
        $('.pregnant .details').hide();
    } else $('.pregnant .details').show();
})
</script>

<h1>Личный кабинет</h1>

<div class="tabs mt">

    {include file='user/personal.tpl' active='user_child'}

    <div class="tab-content active">

        {if $user->child_discount eq Model_User::CHILD_DISCOUNT_NO}
            <p>Я&nbsp;Младенец. РУ &mdash; если я&nbsp;буду знать возраст и&nbsp;пол вашего ребёнка, то&nbsp;Вы&nbsp;будете получать гарантированные скидки
                на&nbsp;товары и&nbsp;отличные подарки, подходящие именно вашему ребенку!</p>
            <p>Специально для вас я&nbsp;готовлю самые лучшие предложения и&nbsp;подарки!<br />
                Вам осталось внести минимальные данные о&nbsp;Вашем малыше и&nbsp;получить первый подарок от&nbsp;меня:
                200&nbsp;рублей скидки к&nbsp;следующему заказу!</p>
        {else}
            <p>Я&nbsp;&mdash; Младенец. Ру, теперь точно знаю, что предложить Вам и&nbsp;Вашему ребёнку.<br />
                {if $user->child_discount eq Model_User::CHILD_DISCOUNT_ON and ! empty($coupon)}Ваш промо-код за&nbsp;заполнение данных о&nbsp;детях&nbsp;&mdash; <strong>{$coupon->name|default:'kidz'}</strong><br />{/if}
                Совсем скоро Вы&nbsp;получите от&nbsp;меня персональные предложения и&nbsp;подарки!</p>
        {/if}

        <form action="" method="post" class="ajax cols">

			<ul class="children">
			{foreach from=$children item=child}
				<li>
                    <label class="l" for="name_{$child->id}">Имя<sup>*</sup></label>
                    <input class="txt" name="name[{$child->id}]" value="{$child->name}" id="name_{$child->id}" />

                    <label class="l">Пол<sup>*</sup></label>
                    <div class="fl" style="margin-bottom:10px;">
                    {foreach from=$sexes item=s key=k}
                        <label class="label" title="{$s}"><i class="radio"></i><input type="radio" name="sex[{$child->id}]" value="{$k}" {if $child->sex == $k} checked="checked"{/if} />{$s}</label>
                    {/foreach}
                    </div>

                    <label class="l" for="birth_{$child->id}">Дата рождения<sup>*</sup><br /><small>(дд-мм-гггг)</small></label>
                    <input class="txt child_birth" name="birth[{$child->id}]" value="{Txt::date_reverse($child->birth)}" id="birth_{$child->id}" maxlength="10" />

                    <a class="ml20 no do" href="/account/child/delete/{$child->id}">Удалить</a>
				</li>
			{/foreach}
				<li class="nc{if not empty($children)} hide{/if}">
					<label class="l">Имя<sup>*</sup></label>
					<input class="txt" name="new_name[0]" value="" id="new_name"/>

					<label class="l">Пол<sup>*</sup></label>
					<div class="fl" style="margin-bottom:10px;">
						{foreach from=$sexes item=s key=k}
							<label class="label" title="{$s}"><i class="radio"></i><input type="radio" name="new_sex[0]" value="{$k}" />{$s}</label>
						{/foreach}
					</div>

					<label class="l">Дата рождения<sup>*</sup><br /><small>(дд-мм-гггг)</small></label>
					<input class="txt child_birth" name="new_birth[0]" value="" maxlength="10" />
				</li>
			</ul>

            <a class="ml20 ok do" onclick="add_child()">+ Добавить ребёнка</a>

            <div class="pregnant cb">
                <label class="label" id="pregnant-check"><i class="check"></i><input name="pregnant" type="checkbox" value="1" {if isset($user->pregnant) AND $user->pregnant eq 1} checked{/if}/> Ждём малыша</label>
                <div class="details"{if isset($user->pregnant) AND $user->pregnant eq 1} style="display:block;"{/if}>
                    <label for="pregnant-week">Срок<sup>*</sup>: </label>
                    <input type="text" name="pregnant_terms" value="{if !empty($pregnant_weeks)}{$pregnant_weeks}{/if}"> неделя
                </div>
            </div>

            <input type="submit" value="Сохранить" class="butt" />
        </form>
    </div>
</div>