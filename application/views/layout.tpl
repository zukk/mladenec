<!DOCTYPE html>
<html>
<head>
    {include file="layout/meta/common.tpl"}
    {include file="layout/meta/$vitrina.tpl"}

    {include file="layout/assets/common.tpl"}
    {include file="layout/assets/$vitrina.tpl"}

    <script>var is_kiosk = {$is_kiosk|intval|default:false}, register_poll = {$register_poll->id|default:0}, IE7 = false, product_load = {$product_load|default:'false'};</script>

    {if $is_kiosk}
        <script src="/j/tinykbd/bililiteRange.js"></script>
        <script src="/j/tinykbd/jquery.sendkeys.js"></script>
        <script src="/j/tinykbd/tinykbd.js"></script>
        <link href="/c/tinykbd.css" rel="stylesheet" type="text/css" />
    {/if}

    <!--[if lt IE 10]>
    <link href="/c/ie_lt_10.css" rel="stylesheet" type="text/css" />
    <script src="/j/ie_lt_10.js"></script>
    <![endif]-->
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="/c/ie7.css?v=010114" />
    <script>IE7 = true;</script>
    <![endif]-->

    {if Kohana::$environment eq Kohana::PRODUCTION}{include file="layout/ga/$vitrina.tpl"}{/if}

</head>
<body {if not empty($main)}class="main"{/if}>
{if Kohana::$environment eq Kohana::PRODUCTION}{literal}<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NW9GWN" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NW9GWN');</script>
<!-- End Google Tag Manager -->
{/literal}
{/if}

{if not empty($sync)}
    {foreach from=$sync item=s}
		<script>
			$(function(){
				$.ajax({
					url: Base64.decode("{$s}"),
					method: 'POST',
					crossDomain: true,
					cache: false,
					data: { domain: "{$host}"},
					xhrFields: { withCredentials: true }
				});
			});
		</script>
    {/foreach}
{/if}

<div id="toptabs">
    <div id="comenu">
		{include file='averburg/user/userpad.tpl'}

        <address id="topcontacts">
            <strong>{$config->phone}</strong>
        </address>

        {$cart|default:''}

        <ul id="sites">
            {if ! empty($is_iframed)}
                {foreach from=Kohana::$hostnames item=domain key=key}
                    {if not empty($domain.is_iframed) and empty( $domain.is_mobile )}
                        <li class="{$key}{if $vitrina == $key} a{/if}">
                            <a href="http://{$domain.host}" >
                                <ins><img src="/i/{$key}/logo.png" alt="" /></ins>
                                <span>{$domain.word|default:''}</span>
                            </a>
                        </li>
                    {/if}
                {/foreach}
            {else}
                {foreach from=Kohana::$hostnames item=domain key=key}
                    {if empty($domain.is_hidden) and empty( $domain.is_mobile )}
                        <li class="{$key}{if $vitrina == $key} a{/if}">
                            <a href="http://{$domain.host}" >
                                <ins><img src="/i/{$key}/logo.png" alt="" /></ins>
                                <span>{$domain.word|default:''}</span>
                            </a>
                        </li>
                    {/if}
                {/foreach}
            {/if}
        </ul>
    </div>
</div>

{include file="layout/$vitrina.tpl"}

{if Kohana::$environment == Kohana::PRODUCTION}
<script src="http://call-tracking.by/calltracking.js"></script>
<script>
if (typeof(CT) != 'undefined'){
    CT.addPhone('+88005556994');
    CT.addPhone('+74956629994');
    CT.addPhone('+74952367292');
    CT.addPhone('+74999758913');
}
</script>
{/if}

</body>
</html>

{$profile|default:''}
