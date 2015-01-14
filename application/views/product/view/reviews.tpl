{foreach from=$comments item=c name=c}
{if $smarty.foreach.c.index lt 5}
<div>
    <div class="data">
        <strong class="review-data-name">{$c->author->name|default:'Аноним'}</strong>

        {if ! empty($params[$c->id]['me'])} ({$params[$c->id]['me']}) {/if}

        <span class="stars"><span style="width:{$c->rating*20}%"></span></span>

        {if ! empty($params[$c->id][1])}
            <div class="good">
                {foreach from=$params[$c->id][1] item=p}<span>+1</span> {$p}<br />{/foreach}
            </div>
        {/if}
        {if ! empty($params[$c->id][-1])}
            <div class="bad">
                {foreach from=$params[$c->id][-1] item=p}<span>-1</span>  {$p}<br />{/foreach}
            </div>
        {/if}
        {if ! empty($params[$c->id][0])}
            <div class="neutral">
                <strong>Использовать с</strong><br />
                {foreach from=$params[$c->id][0] item=p} <span>+1</span> {$p}<br />{/foreach}
            </div>
        {/if}
    </div>

    <div class="desc">
        <small>{$c->time|date_format:'%d-%m-%Y'}</small>
        <h4>{$c->name}</h4>
        <p>{$c->text|nl2br}</p>
        {include file='common/vote.tpl' c=$c votes=$votes}
        <span>Комментарий к товару {$group->name} {$goods[$c->good_id]->name}</span>
    </div>
</div>
{else}
    {assign var=has_more value=$page|default:0+1}
{/if}
{foreachelse}
<p class="cl">Ещё никто не оставил своего мнения, Вы можете быть первым!</p>
{/foreach}

{if ! empty($has_more)}
<a class="do" rel="{$has_more}" id="load_reviews">Загрузить ещё отзывы</a>
{/if}
