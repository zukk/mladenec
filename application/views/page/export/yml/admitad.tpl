<offer id="{$g['id']}" available="{if $g['qty'] eq 0}false{else}true{/if}" {if not empty($section) and $section->is_cloth()}group_id="{$g['group_id']}"{/if}>
    <url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}</url>

    <price>{$g['price']}</price>

    {if not empty($images)}
        {foreach from=$images item=img name=i}
            {if $smarty.foreach.i.iteration lte 10}
                <picture>{$img->get_url()}</picture>
            {/if}
        {/foreach}
    {/if}

    <categoryId type="Own">{$g['section_id']}</categoryId>
    <currencyId>RUR</currencyId>

    <vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>
    {if $g['code'] neq '' AND not empty($label) AND $label eq 'retailrocket'}<vendorCode>{$g['code']|escape:'html'}</vendorCode>{/if}
    <model><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></model>

</offer>