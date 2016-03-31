<offer id="{$g['id']}" available="{if $g['qty'] eq 0}false{else}true{/if}" group_id="{if $section->is_cloth()}{$g['group_id']}{else}{$g['id']+1000000}{/if}">
    <url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}</url>

    <price>{$g['price']}</price>

    {if $g['old_price'] gt 0}
        <oldprice>{$g['old_price']}</oldprice>
    {/if}

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

    <name><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols($g['name']|escape:'html')}]]></name>

</offer>