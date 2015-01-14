{foreach from=$menu item=m}
    <div><strong><a href="/{$m.link}">{$m.name}</a></strong>
    {if !empty($m.children)}
        <ul>
            {foreach from=$m.children item=c}
                <li><a href="/{$c.link}">&sdot; &thinsp; {$c.name}</a></li>
            {/foreach}
        </ul>
    {/if}
    </div>
{/foreach}
