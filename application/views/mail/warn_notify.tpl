<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

<br><h3>Здравствуйте{if ! empty($user->name)}, {$user->name}{/if}!<br></h3>
<p>Вы&nbsp;оставляли заявку на&nbsp;уведомление о&nbsp;поставке товара
	&laquo;<strong>{$g->group_name} {$g->name}</strong>&raquo;
</p>

<p>
    К сожалению, этот товар ещё не появился у нас на складах. Мы уведомим Вас, когда он появится.<br /><br />
	Посмотрите аналогичные товары к <strong>{$g->group_name} {$g->name}</strong>:
</p>
<p>
{if not empty($goods)}
    {if empty($imgs)}{assign var=imgs value=NULL}{/if}
    {if empty($short)}{assign var=short value=NULL}{/if}
<ul>
    {foreach from=$goods item=g name=g}
        {capture assign=name}{$g->group_name|escape:'html'} {$g->name|escape:'html'}{/capture}
        {capture assign=link}http://{$host}{$g->get_link(0)}{/capture}
    <li class="g {if $short}short{/if}" style="float:left; width:170px; margin:0 0 20px 12px; border-radius:5px; border:2px solid #ebebeb; position:relative; list-style:none; background:#fff;font: normal 12px/16px Verdana, Tahoma, Geneva, sans-serif;{if $short}height:auto; min-height:259px;{/if}">
        {*<span class="stars" style="margin-top:10px; margin-left:4px;width:70px; height:12px; line-height: 12px; display:block; background:url(http://{$host}/i/star.png) repeat-x 0 100%; text-align:left;"><span style="width:{$g->rating*20}%" style="display:inline-block; height:12px; background:url(http://{$host}/i/star.png) repeat-x 0 0;"></span></span>*}
        {*<a class="review" title="{'отзыв'|plural:$g->review_qty}" href="http://{$host}{$g->get_review_link()}" style="display:block; white-space:nowrap; color:#acacac; font-size:10px; font-weight:bold; background:url(http://{$host}/i/icon_comments.png) no-repeat 0 50%; position:absolute;right:4px; top:9px; padding-left:20px; font-style:normal;">{$g->review_qty}</a>*}
        <a href="{$link}" title="{$name}"><img src="{$g->get_img($imgs)}" alt="{$name}" style="height:135px; width:auto; max-width:135px; margin:10px auto 5px; display:block;" /></a>
        <a href="{$link}" title="{$name}" style="line-height:18px; display:block; height:75px; overflow:hidden; margin:0 auto; width:155px;color: #0090cc;text-decoration: none;">{$name}</a>
        
        <div class="price" style="display:block; border-top:2px solid #ebebeb; font-size:14px; padding:6px 8px 6px 11px; position:relative; overflow:hidden; height:34px;">
            {if $g->old_price > 0}<span style="display:block; font-size:14px; position:absolute; left:9px; top:6px;"><del style="font-size:12px; left:4px; top:1px;">{$g->old_price|price}</del></span>{/if}
            
            <strong style="display:block; font-size:14px; float:right; position:absolute; right:5px; top:5px; font-weight:normal;">{$g->price|price}</strong>
            {if not empty($price[$g->id])}
                <abbr style="position:absolute; right:5px; bottom:5px;color:#a4cd00; font-size:12px; border-color:#a4cd00; display:block; float:right;">{$price[$g->id]|price}</abbr>
            {/if}
        </div>
    </li>
    {/foreach}
</ul>
{/if}	
	
</p>
<br>

			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>