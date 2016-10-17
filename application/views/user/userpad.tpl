<a href="{Route::url('user_city')}" rel="ajax" data-fancybox-type="ajax" id="current_city">{Session::instance()->get('city')}</a>

<div id="userpad" {if empty($user) and empty($external_account)}class="w"{/if}>
    {if not empty($user)}
		<a href="{Route::url('user')}" class="a">{$user->name}</a>
		<a href="{Route::url('logout')}">Выйти</a>

	{elseif not empty($external_account)}

		<a class="a external">{$external_account.info.name} {$external_account.info.last_name}</a>

	{else}

		{if isset($smarty.get.reg)}{assign var=reg value=1}{else}{assign var=reg value=0}{/if}
                <div class="top-forms-holder">
                    <a rel="user-login">Вход <i class="darr"></i></a>                                
                    <a rel="user-registration" {if $reg}class="open"{/if}>Регистрация <i class="darr"></i></a>

                    {include file='user/registration.tpl'}
                    {include file='user/login.tpl'}
                </div>
		
	{/if}
</div>

