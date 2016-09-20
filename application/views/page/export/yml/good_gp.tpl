<offer id="{$g['id']}" type="vendor.model" available="{if $g['qty'] gt 0}true{else}false{/if}" {if not empty($section) and $section->is_cloth()}group_id="{$g['group_id']}"{/if}>
	<url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}{if not empty($label)}?utm_source={$label}&amp;utm_medium=cpc&amp;utm_term={Txt::translit($g['group_name'], '_')|urlencode}_{Txt::translit($g['name'], '_')|urlencode}&amp;utm_campaign={Txt::translit($section->name, '_')|urlencode}{/if}</url>
	<price>{$g['price']}</price>
	<currencyId>RUR</currencyId>
	<categoryId>{$g['section_id']}</categoryId>

    <delivery>true</delivery>
    <sales_notes>{Model_Action::sales_notes($g.id)}</sales_notes>

	<vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>

	<model><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></model>
	<description><![CDATA[{Txt::clean_rude_symbols($g['desc']|strip_tags|escape:'html')}]]></description>
    <manufacturer_warranty>true</manufacturer_warranty>

</offer>