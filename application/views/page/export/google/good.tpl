<item>
    <g:id>{$g['id']}</g:id>
    <g:title><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols
        ($g['name']|escape:'html')}]]></g:title>
    <g:description><![CDATA[{$g['desc']}]]></g:description>
    <g:link>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}</g:link>

    {if not empty($images)}
        {foreach from=$images item=img name=i}
            {if $smarty.foreach.i.iteration lte 10}
                <g:image_link>{$img->get_url()}</g:image_link>
            {/if}
        {/foreach}
    {/if}

    <g:condition>new</g:condition>
    <g:availability>{if $g['qty'] gt 0}in stock{elseif $g['qty'] lt 0}preorder{else}out of stock{/if}</g:availability>
    <g:price>{$g['price']|string_format:"%.2f"} RUB</g:price>

    <g:brand><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></g:brand>
    <g:mpn>{$g['barcode']|escape:'html'}</g:mpn>

    <g:google_product_category>{$g['google_cat_id']|escape:'html'}</g:google_product_category>
    <g:product_type>{$g['group_name']|escape:'html'}</g:product_type>
</item>

