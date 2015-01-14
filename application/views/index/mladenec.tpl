<div id="ann">
    <a href="{Route::url('action_list')}" class="ih2">Акции</a>
    <div>
        <a href="{Route::url('action_current_list')}"><img src="/i/action_star25.png" alt="WOW-акции" width="25" /></a>
        <p><a href="{Route::url('action_current_list')}">WOW-акции</a><br />Скидки и подарки</p>
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
    <a href="{Route::url('hitz')}"><i></i>Хиты продаж на Младенец.РУ<i></i></a>
	<a class="arr"></a>
	{include file='common/goods.tpl' short=1}
	<a class="arr"></a>
	<div><i></i>
		<table><tr>
	{foreach from=$hitz_sections item=i name=s key=k}
		{assign var=subs value=$top_menu[$i.id]->children|array_keys}
		<td class="
		{if $smarty.foreach.s.iteration eq 1 or $smarty.foreach.s.iteration eq 5}o2{/if}
		{if $smarty.foreach.s.iteration eq 2 or $smarty.foreach.s.iteration eq 4}o1{/if}
		{if $smarty.foreach.s.iteration eq 3}o{/if}
		"><a href="{Route::url('hitz')}#!c={'_'|implode:$subs}" rel="{$i.id}">{$i.name|replace:' и ':'<br />и&nbsp;'}</a></td>
	{/foreach}
		</tr></table>
	</div>
	<i class="rama"></i>
</div>

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
			<strong>{$theme->name}</strong>
			{$comments['questions'][$theme->id][0]->text|truncate:400}
			<a href="{$theme->get_link(0)}" class="l">Ответ магазина<i></i></a>
		</p>
		{if !empty( $comments['answers'][$comments['questions'][$theme->id][0]->id] )}
			<small>{$comments['answers'][$comments['questions'][$theme->id][0]->id][0]->get_answer_by()}</small>
		{/if}
		</div>
	{/foreach}
</div>

{if empty($is_kiosk)}
<div id="widgets">
    {literal}
        <div id="vk"></div>
        <script type="text/javascript" src="//vk.com/js/api/openapi.js?56"></script>
        <script type="text/javascript">VK.Widgets.Group("vk", {mode: 0, width: "300", height: "200"}, 39518389);</script>

        <div id="fb">
            <iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmladenec.ru&amp;width=300&amp;height=210&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=false&amp;header=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:210px;" allowTransparency="true"></iframe>
        </div>
    {/literal}
</div>
{/if}