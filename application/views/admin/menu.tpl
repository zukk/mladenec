<blockquote>
{foreach from=Kohana::message('admin/index') item=group key=header}
    {$group_allow = 0}
    {$li = ''}

    {foreach from=$group item=i}
        {$url = ['model' => $i]}
        {if $user->allow($i)}
            {$group_allow = 1}
            {capture assign=li}
                {$li}
                <li><a href="{Route::url('admin_list', $url)}">{Kohana::message('admin', $i)}</a></li>
            {/capture}
        {/if}
    {/foreach}

    {if $group_allow}
        <ul>
            <li><strong>{$header}</strong></li>
            {$li}
        </ul>
    {/if}
{/foreach}
</blockquote>


