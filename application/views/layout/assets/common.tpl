<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />

{if Kohana::$environment eq Kohana::DEVELOPMENT}

    {foreach from=Controller_Frontend::$scripts item=script}
        <script src="/{$script}"></script>
    {/foreach}
    {foreach from=Controller_Frontend::$css item=css}
        <link href="/{$css}" rel="stylesheet" type="text/css" />
    {/foreach}
{else}
    <script src="/j/script.min.js?$SVN$"></script>
    <link href="/c/style.min.css?$SVN$" rel="stylesheet" type="text/css" />
{/if}

{if empty($user)}
    <script src="//ulogin.ru/js/ulogin.js"></script>
{/if}

<script src="/j/adfox.js"></script>{* вынес отдельно чтобы избежать частичной блокировки при включенном adblock *}
