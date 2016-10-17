<offer id="{$g['id']}" available="{if $g['qty'] gt 0}true{else}false{/if}" type="good">
<url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}</url>
<price>{$g['price']}</price>
<currencyId>RUR</currencyId>
<categoryId>{$g['section_id']}</categoryId>
<delivery>true</delivery>
<pickup>false</pickup>
{if $g['img1600']}<picture>http://www.mladenec-shop.ru/upload/{$g['SUBDIR']}/{rawurlencode($g['FILE_NAME'])}</picture>{/if}
<vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>
<name><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></name>
<description><![CDATA[{Txt::clean_rude_symbols($g['desc']|strip_tags|escape:'html')}]]></description>
</offer>