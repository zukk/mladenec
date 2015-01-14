<script type="text/javascript">
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
</script>

<h1>Личный кабинет</h1>

<div class="tabs mt">
    <div>
        <a class="t" href="{Route::url('user')}">Мои данные</a>
        <a class="t" href="{Route::url('order_list')}">Мои заказы</a>
        <a class="t" href="{Route::url('user_address')}">Мои адреса</a>
        <a class="t active" href="{Route::url('user_child')}">Мои дети</a>
        <a class="t" href="{Route::url('user_action')}">Мои баллы по акции</a>
        <a class="t" href="{Route::url('user_reviews')}">Мои отзывы</a>
    </div>
    <div class="tab-content active">
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

                    <label class="l" for="birth_{$child->id}">Дата рождения<sup>*</sup><br /><small>(гггг-мм-дд)</small></label>
                    <input class="txt child_birth" name="birth[{$child->id}]" value="{$child->birth}" id="birth_{$child->id}" maxlength="10" />

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

					<label class="l">Дата рождения<sup>*</sup><br /><small>(гггг-мм-дд)</small></label>
					<input class="txt child_birth" name="new_birth[0]" value="" maxlength="10" />
				</li>
			</ul>

            <a class="ml20 ok do" onclick="add_child()">+ Добавить ребёнка</a>
			<input type="submit" value="Сохранить" class="butt" />
        </form>
    </div>
</div>