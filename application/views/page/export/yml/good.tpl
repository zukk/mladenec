<offer id="{$g['id']}" type="vendor.model" available="{if $g['qty'] gt 0}true{else}false{/if}" {if not empty($section) and $section->is_cloth()}group_id="{$g['group_id']}"{/if}>
	<url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}{if not empty($label)}?utm_source={$label}&amp;utm_term={Txt::translit($g['group_name'], '_')|urlencode}_{Txt::translit($g['name'], '_')|urlencode}&amp;utm_campaign={Txt::translit($section->name, '_')|urlencode}{/if}</url>
	<price>{$g['price']}</price>
	{$oldprice = round($g['old_price'],2)}{if (($g['price'] * 1.05) lte $oldprice) AND ($g['price']  gte (0.05 * $oldprice))}<oldprice>{$oldprice}</oldprice>{/if}
	<currencyId>RUR</currencyId>
	<categoryId type="Own">{$g['section_id']}</categoryId>
    {if $g['market_category']}<market_category>{$g['market_category']}</market_category>{/if}

        {if not empty($images)}
            {foreach from=$images item=img name=i}
                {if $smarty.foreach.i.iteration lte 10}
                <picture>{$img->get_url()}</picture>
                {/if}
            {/foreach}
        {/if}

    <delivery>true</delivery>
    <delivery-options>
        <option cost="{Model_Zone::min_price(Model_Zone::DEFAULT_ZONE, $g.price)}" days="{if $g.qty == -1}3-5{else}0{/if}" order-before="12"/>
    </delivery-options>
    <sales_notes>{Model_Action::sales_notes($g.id)}</sales_notes>

    {if not empty($label) and $label eq 'ozon.yml'}{* количество - нужно только для озона *}
        <qty>{if $g['qty'] eq -1}10{else}{$g['qty']}{/if}</qty>
    {/if}

	<vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>
    {if $g['code'] neq '' AND not empty($label) AND $label eq 'retailrocket'}<vendorCode>{$g['code']|escape:'html'}</vendorCode>{/if}
	<model><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></model>
	<description><![CDATA[{Txt::clean_rude_symbols($g['desc']|strip_tags|escape:'html')}]]></description>
    <manufacturer_warranty>true</manufacturer_warranty>

    {if not empty($section) and ($section->is_cloth() or $section->id eq Model_Section::CLOTHS_ROOT)}
		{if not empty( $good_filter )}
			{foreach from=$good_filter key=filter_id item=values_ids}
				{foreach from=$values_ids item=value}
					{if ! empty( $value['name'] )}
						{if $filter_id eq $smarty.const.EXPORTXML_SEX}
							<param name="{$filter_labels[$filter_id]}">{$value['name']}</param>
						{elseif $filter_id eq $smarty.const.EXPORTXML_SIZE}
							<param unit='{$value['unit']}' name="{$filter_labels[$filter_id]}">{$value['name']}</param>
						{elseif $filter_id eq $smarty.const.EXPORTXML_GROWTH}
							<param unit='{$value['unit']}' name="{$filter_labels[$filter_id]}">{$value['name']}</param>
						{elseif $filter_id eq $smarty.const.EXPORTXML_COLOR}
							<param name="{$filter_labels[$filter_id]}">{$value['name']}</param>
						{/if}
					{/if}
				{/foreach}
			{/foreach}
		{/if}
	{/if}
        {if  not empty($label) and $label eq 'retailrocket'}
            <param name="cat_url">{$section->get_link(0)}</param>
            <param name="rating">{$g['rating']}</param>
            <param name="id1c">{$g['id1c']}</param>
            <param name="review_count">{$g['review_qty']}</param>

            {capture assign=qty}{if $g['qty'] eq -1}10{else}{$g['qty']}{/if}{/capture}
            {assign var="stock_status" value=$qty|qty:0:1}
            <param name="stock">{$stock_status['class']}</param>            
            {if not empty($other_filters) AND  count($other_filters)>0}
                {foreach  from=$other_filters key=name item=value}
            <param name="{$name|escape:'html'}"><![CDATA[{Txt::clean_rude_symbols( ($value|implode:', ')|escape:'html')}]]></param>                
                {/foreach}
            {/if}
        {/if}
</offer>