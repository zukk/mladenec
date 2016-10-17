{include file='common/retag.tpl'}

{include file="common/slider.tpl"}

<div id="maina">

	<div class="na"  style="width:200px">
		<div id="last_comment" style="margin-top:0">
			<a class="big" href="{Model_Comment::get_list_link()}">Отзывы</a>

			{foreach from=$comments['themes'] item=theme}
				<p class="comment">
					<a href="{$theme->get_link(0)}">{$theme->user_name}</a><i></i>
					<strong>{$theme->name}</strong>
					{$comments['questions'][$theme->id][0]->text|truncate:100}
					<a href="{$theme->get_link(0)}" class="l">Ответ магазина<i></i></a>
				</p>
				{if ! empty( $comments['answers'][$comments['questions'][$theme->id][0]->id] )}
					<small>{$comments['answers'][$comments['questions'][$theme->id][0]->id][0]->get_answer_by()}</small>
				{/if}
			{/foreach}
		</div>
	</div>

	<div class="na" style="width:360px; margin-right:25px;">
		<a class="big" href="{Model_New::get_list_link()}">Новости</a>
		<ul>
			{foreach from=$news item=n}
				<li>
					<small>{$n->date|date_ru}</small>
					{$n->get_link()}
					<a href="{$n->get_link(0)}">{$n->image->get_img()}</a>
					<p>{$n->preview}</p>
				</li>
			{/foreach}
		</ul>
	</div>

	<div class="na" style="width:340px;" >
		<a class="big" href="{Route::url('action_list')}">Акции</a>
		<ul class="act">
			<li>
				<img class="fr" src="/i/action_star25.png" alt="Акция с подарками" width="25" />
				<a href="{Route::url('action_list')}">{$config->actions_header|default:'Акции месяца'}</a>
				{$config->actions_subheader|nl2br}
			</li>
			{foreach from=$actions item=n}
				<li>
					{if $n->type == 1}<img class="fr" src="/i/podarok.png" alt="Акция с подарками" width="25" />{/if}
					{if $n->type == 2}<img class="fr" src="/i/sale.png" alt="Акция со скидкой" width="25" />{/if}
					{$n->get_link()}
					{$n->preview|nl2br}
				</li>
			{/foreach}
		</ul>
	</div>

</div>
