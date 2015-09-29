<script type="text/javascript">
{literal}
$(document).ready(function () {
    $('#review a.more_input').click(function() {
        $(this).prev('input').clone().val('').insertBefore(this);
    })
});
{/literal}
</script>

<h2>Оставить отзыв о товаре</h2>

{if empty($sent)}
<form action="/review/add/{$good_id}" method="post" id="review" class="ajax cols small">

    <div>
        <label for="name" class="l">Заголовок отзыва <sup>*</sup></label>
        <input type="text" id="name" name="name" value="{$i->name}" class="txt"/>

        <label id="mark" class="l" for="rating">Оценка <sup>*</sup></label>
		<input type="hidden" id="review_mark" name="rating" value="0" />
			   
		<label class="priority">
			<span rel="review_mark" title="1"></span>
			<span rel="review_mark" title="2"></span>
			<span rel="review_mark" title="3"></span>
			<span rel="review_mark" title="4"></span>
			<span rel="review_mark" title="5"></span>		
		</label>
    </div>

    <div class="good third cl">
        <span>Положительный отзыв</span>
        <ul>
        {if ! empty($params[1])}
        {foreach from=$params[1] item=p key=id}
            <li>
                <label title="{$p}" class="label"><i class="check"></i><input type="checkbox" name="good[]" value="{$id}" /> {$p}</label>
            </li>
        {/foreach}
        {/if}
            <li><input type="text" class="txt misc" name="good_add[]" value="" /><a class="more_input">+</a></li>
        </ul>
    </div>
    <div class="bad third">
        <span>Отрицательный отзыв</span>
        <ul>
            {if ! empty($params[-1])}
            {foreach from=$params[-1] item=p key=id}
                <li>
                    <label title="{$p}" class="label"><i class="check"></i><input type="checkbox" name="bad[]" value="{$id}" /> {$p}</label>
                </li>
            {/foreach}
            {/if}
            <li><input type="text" class="txt misc" name="bad_add[]" value=""/><a class="more_input">+</a></li>
        </ul>
    </div>
    <div class="neutral third">
        <span>Использовать с</span><br />
        <ul>
            {if ! empty($params[0])}
            {foreach from=$params[0] item=p key=id}
                <li>
                    <label title="{$p}" class="label"><i class="check"></i><input type="checkbox" name="neutral[]" value="{$id}" /> {$p}</label>
                </li>
            {/foreach}
            {/if}
            <li><input type="text" class="txt misc" name="neutral_add[]" value="" /><a class="more_input">+</a></li>
        </ul>
    </div>
    <div class="neutral third cl">
        <span>О себе</span>
        <ul>
            {if ! empty($params[1])}
            {foreach from=$params['me'] item=p key=id}
                <li>
                    <label title="{$p}" class="label"><i class="check"></i><input type="checkbox" name="me[]" value="{$id}" /> {$p}</label>
                </li>
            {/foreach}
            {/if}
            <li><input type="text" class="txt misc" name="me_add[]" value=""/><a class="more_input">+</a></li>
        </ul>
    </div>

    <div id="review_text">
        <label for="text" class="l cl">Текст отзыва <sup>*</sup></label>
        <textarea id="text" name="text" cols="40" rows="4" class="txt">{$i->text}</textarea>
        {if not $user}
            <label for="captcha" class="l"><img src="/captcha" alt="" /></label>
            <div class="fl">
                <label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br />
                <input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt"/>
            </div>
        {/if}
        <input name="send" value="Отправить" type="submit" class="butt small" />
		<script>
			$(function(){
				$('[name=send]').click(function(){
					if( $('[name=rating]').val() == 0 ){
						
						alert('Пожалуйста, поставьте Оценку');
						return false;
					}
				});
			});
		</script>
    </div>
</form>

    {else}

<div class="ok">
    Спасибо что уделили нам время!<br />
    Ваше мнение о&nbsp;товаре принято и&nbsp;будет опубликовано после проверки модератором.<br />
    Мы публикуем любые мнения, не&nbsp;содержащие ненормативной лексики.<br />
    Отзыву присвоен номер {$sent}.<br />
</div>

{/if}