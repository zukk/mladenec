<form action="">
    <h2>Карта баннеров</h2>

    <table id="admap">
    <tr>
        <td colspan="3">
            <a href="?code=banner_950X60_1">banner_950X60_1 [1]</a>
            {$ad->stat('banner_950X60_1')}
        </td>
    </tr>
    <tr>
        <td>тут меню</td>
        <td>
            <a href="?code=banner_360X256_2">banner_360X256 [2]</a>
            {$ad->stat('banner_360X256_2')}
        </td>
        <td>
            <a href="?code=banner_360X256_3">banner_360X256 [3]</a>
            {$ad->stat('banner_360X256_3')}
        </td>
    </tr>
    <tr>
        <td>
            <a href="?code=banner_300X210_4">banner_300X210 [4]</a>
            {$ad->stat('banner_300X210_4')}
        </td>
        <td>
            <a href="?code=banner_300X210_5">banner_300X210 [5]</a>
            {$ad->stat('banner_300X210_5')}
        </td>
        <td>
            <a href="?code=banner_300X210_6">banner_300X210 [6]</a>
            {$ad->stat('banner_300X210_6')}
        </td>
    </tr>
    </table>

    <h2>Список баннеров {if ! empty($smarty.get.code)}{$smarty.get.code}{/if}</h2>

    <!--a href="ad/add" class="add">+ добавить баннер</a-->
    <table id="list">
    <tr>
        <td>#</td>
        <th>тип</th>
        <th>срок</th>
        <th>активность</th>
        <th>название</th>
        <th>картинка</th>
        <th>показ</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><small>{$i->code}</small></td>
        <td><small>{$i->from|default:'нет'}</small> - <br /><small>{$i->to|default:'нет'}</small></td>

        <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
        <td><a href="ad/{$i->id}">{$i->name|default:'<без названия>'}</a></td>
        <td>
            {assign var=w value=$i->code|regex_replace:'~^.*?(\d+)X(\d+).*$~i':'$1'}
            {assign var=h value=$i->code|regex_replace:'~^.*X(\d+).*$~i':'$1'}
            {assign var=url value=$i->image->get_url()}

            {if ! preg_match('~swf$~', $url)}
                <a id="da{$i->id}" href="{$i->url}"{if $i->newtab eq 1} target="_blank"{/if}><img style="width:{$w}px;height:{$h}px" src="{$url}"  alt="" /></a>
            {else}
                <object style="width:{$w}px;height:{$h}px" id="da{$i->id}" width="{$w}" height="{$h}" type="application/x-shockwave-flash" data="{$url}">
                    <param name="movie" value="{$url}">
                    <param name="wmode" value="transparent">
                </object>
            {/if}


        </td>
        <td>{if $i->is_ok()}ДА{/if}</td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Баннеры')}
