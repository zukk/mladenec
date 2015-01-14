<h1>Порядок и видимость категорий определяется из 1C</h1>
<script type="text/javascript">
{literal}
$(document).ready(function() {
    var resort = function(event, ui) {
        var ul = $(ui.item).parent();
        $('> li > input[name^=sort]', ul).each(function(index, item) { $(this).val(index)});
    };
    $(function() {
        $(".sortable").sortable({stop: resort});
        $(".sortable").disableSelection();
        $(".sortable ul").sortable({stop: resort});
        $(".sortable ul").disableSelection();
    });
});
{/literal}
</script>
<form action="" id="form">
    Выберите витрину:
    <select name="vitrina" onchange="$('#form').submit()">
    {foreach from=$vitrinas item=vitrina key=name}<option value="{$name}" {if $smarty.get.vitrina|default:'mladenec' eq $name}selected="selected"{/if}>{$vitrina.host}</option>{/foreach}
    </select>
</form>

<form action="" method="post" id="form">

    <input type="submit" value="Порядок категорий определяется из 1С" name="save" class="hidden fixed"/>
    <ul class="cb sortable" id="parent0">
    {assign var=last_parent value=0}
    {foreach from=$list item=i name=i}
        <li> <input name="sort[{$i->id}]" value="{$i->sort}" type="hidden" size="1" maxlength="3" />
            <a href="/od-men/{$m}/{$i->id}" style="font-size:2em;">{$i->name|default:'<без названия>'}</a>{if not $i->active}[откл]{/if}
            <input name="active[{$i->id}]" type="hidden" value="1" {if $i->active}checked="checked"{/if} />
            {if  $i->children}
            <ul>
                {foreach from=$i->children item=k}
                <li><input name="sort[{$k->id}]" type="hidden" value="{$k->sort}" size="1" maxlength="3" />
                    <a href="/od-men/{$m}/{$k->id}">{$k->name}</a>{if not $k->active}[откл]{/if}
                    <input name="active[{$k->id}]" type="hidden" value="1" {if $k->active}checked="checked"{/if} />
                {/foreach}
            </ul>
            {/if}
        </li>
        {if $smarty.foreach.i.iteration mod 4 eq 0}<li class="cb" style="width:100%"></li>{/if}
    {/foreach}
    </ul>
</form>

