<div class="cb"><a name="tabs"></a></div>
<div class="tabs mt cb">
    <div>
        {foreach from=$sectionTabs item=tab}
            {if $tab eq "Полное описание"}
                <a class="active t">{$tab}</a>
            {elseif not empty($goodTabs[$tab]) or $tab eq "Отзывы"}
                <a {if $tab eq "Отзывы"} id="reviews"{/if} class="t">{$tab}</a>
            {/if}
        {/foreach}
        {if not empty($serts)}<a class="t">Сертификаты соответствия</a>{/if}
    </div>

	{foreach from=$sectionTabs item=tab}

    {if not empty($goodTabs[$tab])}

	<div class="tab-content{if $tab eq "Полное описание"} active{/if}">

        <div {if $tab eq "Полное описание"}property="description"{/if} class="txt">
            {if $tab eq "Полное описание"}
                {Model_Good_Text::desc($goodTabs[$tab])}

                {if not empty($filters)}
                    {if ! empty($consul)}<p>{$consul}</p>{/if}

                    {foreach from=$filters key=fname item=vals}
                        {assign var=under value=$fname|strpos:'_'}
                        {if $under}{assign var=fname value=$fname|mb_substr:$under}{/if}

                        <p>
                            <strong>{$fname}:</strong> {', '|implode:$vals}
                        </p>
                    {/foreach}
                {/if}
            {else}
                {$goodTabs[$tab]|default:""}
            {/if}
		</div>

	</div>

    {elseif $tab eq "Отзывы"}

        <div class="tab-content">
			<div class="review">
				{if empty($infancybox) AND ( ! isset($is_quickview) OR ! $is_quickview)}
					<a data-url="{Route::url('review', ['id' => {$prop->id}])}" id="review_butt" class="small i i_pen appendhash" href="#" rel="ajax"
                       data-fancybox-type="ajax" style="margin-left: 0; padding: 20px 0 20px 10px; font-size: 1.2em; color: #3fb3d5;">
                        <b>&#12297;</b> <span style="text-decoration: underline;">Написать отзыв</span>
                    </a>
				{/if}
				<a name="reviews"></a>
                <input id="group_id" value="{$cgood->group_id}" type="hidden" />
				<input name="group" id="reviews_for" type="hidden" value="{if $cgood->is_cloth() || $cgood->big}1{else}0{/if}" />

				<div><i class="load"></i></div>
			</div>
	    </div>
	{/if}
	{/foreach}

    {if not empty($serts)}
		<div class="tab-content">
			<div class="txt sert oh">
				{foreach from=$serts item=s}
					<a href="{$s->big->get_img(0)}" title="{$s->name}" rel="sert">{$s->small->get_img()}</a>
				{/foreach}
			</div>
		</div>
	{/if}

</div>
