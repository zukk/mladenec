<offer id="{$g['id1c']}" type="vendor.model" available="{if $g['qty'] gt 0 or $g['qty'] eq -1}true{else}false{/if}">
	<url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}{if not empty($label)}?utm_source={$label}&amp;utm_term={$g['id']}{/if}</url>
	<price>{$g['price']}</price>
	{$oldprice = round($g['old_price'],2)}{if (($g['price'] * 1.05) lte $oldprice) AND ($g['price']  gte (0.05 * $oldprice))}<oldprice>{$oldprice}</oldprice>{/if}
	<currencyId>RUR</currencyId>
	<categoryId type="Own">{$g['section_id']}</categoryId>
	{if $g['img1600']}<picture>http://www.mladenec-shop.ru/upload/{$g['SUBDIR']}/{rawurlencode($g['FILE_NAME'])}</picture>{/if}
	<delivery>true</delivery>
    <qty>{if $g['qty'] eq -1}10{else}{$g['qty']}{/if}</qty>
	<vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>
	<model><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></model>
	<description><![CDATA[{Txt::clean_rude_symbols($g['desc']|strip_tags|escape:'html'|mnemonicate)}]]></description>
</offer>