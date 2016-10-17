{foreach from=$users item=u}
{capture assign=name}{$u->last_name} {$u->name} {$u->second_name}{/capture}
{$u->id}©{$name}©{$u->phone}|{$u->phone2}©{$u->email}©{if $u->status_id == 1}gold{else}stan{/if}

{/foreach}
