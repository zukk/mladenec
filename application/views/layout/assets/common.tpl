<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />

{if Kohana::$environment eq Kohana::DEVELOPMENT}
	{foreach from=Controller_Frontend::$scripts item=script}
	<script src="{Model_File::get_host()}/{$script}"></script>
	{/foreach}
	{foreach from=Controller_Frontend::$css item=css}
	<link href="{Model_File::get_host()}/{$css}" rel="stylesheet" type="text/css" />
	{/foreach}
{else}
	<script src="{Model_File::get_host()}/j/script.min.js?$SVN$"></script>
	<link href="{*Model_File::get_host()*}/c/style.min.css?$SVN$" rel="stylesheet" type="text/css" />
{/if}
