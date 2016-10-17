<offer id="{$g['id']}" available="true">
    <url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}</url>
    <price>{$g['price']|string_format:"%d"}</price>
    {$oldprice = round($g['old_price'],2)}
    {if (($g['price'] * 1.05) lte $oldprice) AND ($g['price']  gte (0.05 * $oldprice))}
        <oldprice>{$oldprice}</oldprice>
    {/if}
    <currencyId>RUR</currencyId>
    <categoryId>{$g['wiki_cat_id']}</categoryId>

    {if not empty($images)}
        {foreach from=$images item=img name=i}
            {if $smarty.foreach.i.iteration lte 10}
                <picture>{$img->get_real_url()}</picture>
            {/if}
        {/foreach}
    {/if}

    <model><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')} {Txt::clean_rude_symbols
        ($g['name']|escape:'html')}]]></model>

    <typePrefix><![CDATA[{Txt::clean_rude_symbols($g['group_name']|escape:'html')}]]></typePrefix>
    <vendor><![CDATA[{Txt::clean_rude_symbols($g['brand_name']|escape:'html')}]]></vendor>
    <vendorCode>{$g['barcode']|escape:'html'}</vendorCode>
    <description><![CDATA[{Txt::clean_rude_symbols($g['desc']|strip_tags|escape:'html')}]]></description>
    {if $g['qty'] gt 0}<stock>{$g['qty']|escape:'html'}</stock>{/if}

    {*if not empty($section) and ($section->is_cloth() or $section->id eq Model_Section::CLOTHS_ROOT)*}
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
    {*/if*}

    <manufacturer_warranty>true</manufacturer_warranty>
    {if !empty($g['country_name'])}
        <country_of_origin><![CDATA[{Txt::clean_rude_symbols($g['country_name']|escape:'html')}]]></country_of_origin>
    {/if}

    {if  not empty($label) and $label eq 'findologic'}
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