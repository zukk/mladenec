<div id="menu">
    <div id="pp">
    {foreach from=$menu item=m}
    <a href="/{$m.link}">{$m.name}</a>
    {if ! empty($m.children)}
        <ul>
            {foreach from=$m.children key=c_id item=c}
                {if not ($is_kiosk and ($c_id eq 5))}
                    <li><a href="/{$c.link}">&sdot; &thinsp; {$c.name}</a>
                {/if}
            {/foreach}
        </ul>
    {/if}
    {/foreach}
    </div>
    {if $vitrina eq 'kotdog'}
        <a href="/" id="logo"><img src="/i/kotdog/head.png" alt="Kot-dog.ru" /></a>
    {/if}
</div>
