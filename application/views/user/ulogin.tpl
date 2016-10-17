<div class="ulogin">
{if $cfg.type eq 'window'}

    <a id="{$uniq_id}" href="#" x-ulogin-params="{$params}">
        <img src="//ulogin.ru/img/button.png" width=187 height=30 alt="МультиВход" />
    </a>

{else}

    <div id="{$uniq_id}" x-ulogin-params="{$params}"></div>

{/if}
</div>