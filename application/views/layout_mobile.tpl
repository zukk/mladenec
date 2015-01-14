<!DOCTYPE html>
<html>
<head>
    {include file="layout/meta/$vitrina.tpl"}
    {include file="layout/assets/$vitrina.tpl"}
    {*if Kohana::$environment eq Kohana::PRODUCTION}{include file="layout/ga/$vitrina.tpl"}{/if*}
</head>
<body>
	<header class="mhead">
		<a title='Младенец.ру' alt='Младенец.ру' href="/" class="mlogo"></a>
		<a title="Корзина" alt="Корзина" href="/personal/basket.php" class="mcart"></a>
		<a title="Поиск" alt="Поиск" href="?search" class="msearch"></a>
		<a title="Меню" alt="Меню" href="?menu" class="mmenu"></a>
	</header>
	
	{include file="layout/$vitrina.tpl"}

	<footer class="mfooter">
		<a href='#' class='mreverse-call'>Обратный звонок</a>
		<a href='http://mladenec.ru' class='mfull-version'>Полная версия</a>
	</footer>
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

{*$profile|default:''*}
