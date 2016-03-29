{include file='common/retag.tpl'}

<div id="ann">
    <a href="{Route::url('action_list')}" class="ih2">Акции</a>
    <div>
        <a href="{Route::url('action_list')}"><img src="/i/action_star25.png" alt="WOW-акции" width="25" /></a>
        <p><a href="{Route::url('action_list')}">WOW-акции</a><br />Скидки и подарки</p>
    </div>

    <a href="{Route::url('news')}" class="ih2">Новости</a>
    <div>
        <a href="{$new->get_link(0)}" style="background-size:cover; background-image:url({$new->image->get_img(0)})"></a>
        <p>{$new->get_link()} {$new->preview}</p>
    </div>

    <a href="{Route::url('novelty')}" class="ih2">Новинки</a>
    <div>
        <a href="{Route::url('novelty')}"><img src="/i/excl.png" alt="Новинки" width="15" height="29" /></a>
        <p><a href="{Route::url('novelty')}">Свежие поступления</a><br />от Младенец. РУ</p>
    </div>
</div>

{include file="common/slider.tpl"}

<div id="hitz">
    {$hitz}
</div>

{if not empty($config->rr_enabled)}
    <div class="cl rr_slider" title="Рекомендуем Вам:" data-func="PersonalRecommendation" data-param="{$smarty.cookies.rrpusid}"></div>
{/if}

<div id="ia">
	<a class="ih2" href="{Route::url('article')}">Общение</a>
	{foreach from=$articles item=n name=n}
	<div>
		<a href="{$n->get_link(0)}">{$n->minimg->get_img()}</a>
	    {$n->get_link()}
	    <p>{$n->preview|nl2br}</p>
		<i></i>
	</div>
	{/foreach}
</div>

<div id="ic">
	<a class="ih2" href="{Route::url('comments')}">Отзывы клиентов</a>
	{foreach from=$comments['themes'] item=theme}
		<div>
		<p class="comment">
			<a href="{$theme->get_link(0)}">{$theme->user_name}</a><i></i>
			<span>{$theme->name}</span>
			{$comments['questions'][$theme->id][0]->text|truncate:400}
			<a href="{$theme->get_link(0)}" class="l">Ответ магазина<i></i></a>
		</p>
		{if not empty($comments['answers'][$comments['questions'][$theme->id][0]->id])}
			<small>{$comments['answers'][$comments['questions'][$theme->id][0]->id][0]->get_answer_by()}</small>
		{/if}
		</div>
	{/foreach}
</div>

<div id="widgets">
    <div id="vk"></div>
    <div id="fb"></div>
</div>
<div id="insta_widget">
    <iframe src="/inwidget/index.php?width=100&inline=1&view=4toolbar=false&preview=small" scrolling="no" frameborder="no" style="border:none;width:100px;height:400px;overflow:hidden;"></iframe>
</div>


{if not empty($config->rr_enabled)}
    <div class="cl rr_slider" title="Покупают сейчас:" data-func="ItemsToMain"></div>
{/if}

<script>
    var google_tag_params = {
        ecomm_pagetype: 'home'
    };
</script>