<div>
    {foreach from=Kohana::message('user/personal') key=route item=name}
        {assign var=class value='t'}
        {if $active == $route}
            {assign var=class value='t active'}
        {/if}
    {HTML::anchor(Route::url($route), $name, ['class' => $class])}
    {/foreach}
</div>
