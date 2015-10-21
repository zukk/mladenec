<!DOCTYPE html>
<html>
<head>
    {include file="layout/meta/common.tpl"}
    {include file="layout/meta/$vitrina.tpl"}

    {include file="layout/assets/common.tpl"}
    {include file="layout/assets/$vitrina.tpl"}

    <script>
		var register_poll = {$register_poll->id|default:0},
			IE7 = false, 
			product_load = {$product_load|default:'false'},
            uid = {$user->id|intval|default:0}
			{if not empty($config->rr_enabled)}
                , RetailRocket = 1
            {/if}
        ;
	</script>

    <!--[if lt IE 10]>
    <link href="/c/ie_lt_10.css" rel="stylesheet" type="text/css" />
    <script src="/j/ie_lt_10.js"></script>
    <![endif]-->
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="/c/ie7.css?v=010114" />
    <script>IE7 = true;</script>
    <![endif]-->

</head>
<body {if not empty($main)}class="main"{/if}>
{if Kohana::$environment eq Kohana::PRODUCTION}
    {$dataLayer|default:''}
    <!-- Google Tag Manager -->
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NW9GWN" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function(w,d,s,l,i){ w[l]=w[l]||[];w[l].push({ 'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NW9GWN');</script>
    <!-- End Google Tag Manager -->
{/if}
{if $config->rr_enabled}
    {rrapi::tracking()}
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
		{include file='user/userpad.tpl'}

        <address id="topcontacts">
            <span>{$config->phone}</span>
        </address>

        {$cart|default:''}

        <ul id="sites">
            {foreach from=Kohana::$hostnames item=domain key=key}
                {if empty($domain.is_hidden) and empty($domain.is_mobile)}
                    <li class="{$key}{if $vitrina == $key} a{/if}">
                        {if $vitrina neq $key}<!--noindex-->{/if}
                        <a href="http://{$domain.host}"{if $vitrina neq $key} rel="nofollow"{/if}>
                            <ins><img src="/i/{$key}/logo.png" alt="" /></ins>
                            <span>{$domain.word|default:''}</span>
                        </a>
                        {if $vitrina neq $key}<!--/noindex-->{/if}
                    </li>
                {/if}
            {/foreach}
        </ul>
    </div>
</div>

{include file="layout/$vitrina.tpl"}

<script>
{if not empty($user)}
    executeGoodsTopBar();
{/if}
    if (typeof(impressionsObject) == "undefined" ){
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            userId: uid,
            event: 'dataload'
        });
    }
</script>
</body>
</html>

{$profile|default:''}
