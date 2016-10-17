<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="{'Y-m-d H:i'|date}">
    <shop>
        <name>ООО &quot;TД Младенец.РУ&quot;</name>
        <company>Младенец.РУ</company>
        <url>http://www.mladenec-shop.ru/</url>

        <currencies>
            <currency id="RUR" rate="1" plus="0"/>
        </currencies>

        <categories>
            {foreach from=$catalog item=c}
                {if not empty($c.id) AND not empty($c.name)}
                    <category id="{$c.id}">{$c.name|escape:'html'}</category>
                    {if $c.children}
                        {foreach from=$c.children item=ch}
                            {if not empty($ch.id) AND not empty($ch.name)}
                                <category id="{$ch->id}" parentId="{$c.id}">{$ch->name|escape:'html'}</category>
                            {/if}
                        {/foreach}
                    {/if}
                {/if}
            {/foreach}
        </categories>

        <local_delivery_cost>350</local_delivery_cost>

        <offers>
            {foreach from=$goods item=g}
            <offer id="{$g['id']}" type="vendor.model" available="{if $g['qty'] gt 0}true{else}false{/if}">
                <url>http://www.mladenec-shop.ru{Route::url('product',['translit'=>$g['translit'],'group_id'=>$g['group_id'],'id'=>$g['id']])}</url>
                <price>{$g['price']}</price>
                <currencyId>RUR</currencyId>
                <categoryId type="Own">{$g['section_id']}</categoryId>
                {if $g['img255']}<picture>http://www.mladenec-shop.ru/upload/{$g['SUBDIR']}/{$g['FILE_NAME']}</picture>{/if}
                <delivery>true</delivery>
                <vendor>{$g['brand_name']|escape:'html'}</vendor>
                <model>{$g['group_name']|escape:'html'} {$g->name|escape:'html'}</model>
                <description>{$g['desc']|strip_tags|escape:'html'}</description>
            </offer>
            {/foreach}
        </offers>
    </shop>
</yml_catalog>
