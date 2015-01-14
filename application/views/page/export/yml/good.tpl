<offer id="{$g['id']}" type="vendor.model" available="{if $g['qty'] gt 0}true{else}false{/if}" {if $section->export_type eq Model_Section::EXPORTYML_CLOTHERS}group_id="{$g['group_id']}"{/if}>
	<url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}{if not empty($label)}?utm_source={$label}&amp;utm_term={$g['id']}{/if}</url>
	<price>{$g['price']}</price>
	{$oldprice = round($g['old_price'],2)}{if (($g['price'] * 1.05) lte $oldprice) AND ($g['price']  gte (0.05 * $oldprice))}<oldprice>{$oldprice}</oldprice>{/if}
	<currencyId>RUR</currencyId>
	<categoryId type="Own">{$g['section_id']}</categoryId>
	{if $g['img1600']}<picture>http://www.mladenec-shop.ru/upload/{$g['SUBDIR']}/{rawurlencode($g['FILE_NAME'])}</picture>{/if}
	<delivery>true</delivery>
    {if not empty($label) and $label eq 'ozon.yml'}
        <qty>{if $g['qty'] eq -1}10{else}{$g['qty']}{/if}</qty>
    {/if}
	<vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>
	<model><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></model>
	<description><![CDATA[{Txt::clean_rude_symbols($g['desc']|strip_tags|escape:'html')}]]></description>
	{if not empty($section) and $section->export_type eq Model_Section::EXPORTYML_CLOTHERS}
		{if not empty( $good_filter )}
			{foreach from=$good_filter key=filter_id item=values_ids}
				{foreach from=$values_ids item=value}
					{if !empty( $value['name'] )}
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
</offer>