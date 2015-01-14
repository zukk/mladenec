{foreach from=$action item=icon key=action_id}
    <abbr class="q" abbr="<b>{$icon.name}</b><br />{$icon.preview}"><a href="{Route::url('action', ['id' => $action_id])}"><img src="/i/action/type/{$icon.type}.png" alt="Акция" /></a></abbr>
{/foreach}
