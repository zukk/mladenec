<script>
$(document).ready(function() {
    var resort = function(event, ui) {
        var ul = $(ui.item).parent();
        $('> li > input[name^=sort]', ul).each(function(index, item) { $(this).val(index)});
    };
    $(function() {
        var sortme = $(".sortable, .sortable ul");
        sortme.sortable({ stop: resort});
        sortme.disableSelection();
    });
});
</script>

<a href="/od-men/menu/add">+ Добавить страницу</a>

<form action="" method="post" style="z-index:0;">

    <ul class="sortable">
    {foreach from=$list item=i}
        <li>&uarr;&darr; № <input name="sort[{$i.id}]" value="{$i.sort}" size="1" maxlength="3" />
            <a href="/od-men/{$m}/{$i.id}">{$i.name}</a>
            <input name="show[{$i.id}]" type="checkbox" value="1" {if $i.show}checked="checked"{/if} />
            <a class="toggler"></a>
            {if  $i.children}
                <ul>
                    {foreach from=$i.children item=k}
                    <li>&uarr;&darr; № <input name="sort[{$k.id}]" value="{$k.sort}" size="1" maxlength="3" />
                        <a href="/od-men/{$m}/{$k.id}">{$k.name}</a>
                        <input name="show[{$k.id}]" type="checkbox" value="1" {if $k.show}checked="checked"{/if} />
                    {/foreach}
                </ul>
            {/if}
        </li>
    {/foreach}
    </ul>

    <input type="submit" value="Сохранить" name="save" />

</form>

