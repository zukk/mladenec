<div id="userpad" {if empty($user) and empty($external_account)}class="w"{/if}>
	{if not empty($user)}
		<script type="text/javascript">
			{literal}
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
			{/literal}
			
			ga('set', '&uid', {$user->id});
		</script>

		<a href="{Route::url('user')}" class="a">{$user->name}</a>
		<a href="{Route::url('logout')}">Выйти</a>

	{elseif not empty($external_account)}

		<a class="a external">{$external_account.info.name} {$external_account.info.last_name}</a>

	{else}

		{if isset($smarty.get.reg)}{assign var=reg value=1}{else}{assign var=reg value=0}{/if}

		<a rel="user-login">Вход <i class="darr"></i></a>

		<a rel="user-registration" {if $reg}class="open"{/if}>Регистрация <i class="darr"></i></a>

		{include file='averburg/user/login.tpl'}
		<iframe name="dummy" style="display:none;"></iframe>
		{include file='averburg/user/registration.tpl'}
	{/if}
</div>
