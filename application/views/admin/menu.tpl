<nav class="nav" style="margin-left:1em;">
{foreach from=Kohana::message('admin/index') item=group key=header}
    {$group_allow = 0}
    {$li = ''}

    {foreach from=$group item=i}
        {$url = ['model' => $i]}
        {if $user->allow($i)}
            {$group_allow = 1}
            {capture assign=li}
                {$li}
                <li><a href="{Route::url('admin_list', $url)}">
                        {if $header eq Kohana::message('admin', $i)}
                        <h6>
                        {/if}
                            {Kohana::message('admin', $i)}
                        {if $header eq Kohana::message('admin', $i)}
                            </h6>
                        {/if}
                    </a>
                </li>
            {/capture}
        {/if}
    {/foreach}

    {if $group_allow}
        <ul class="pause" style="border:thin solid #ccc; padding:0 .5em">
            {$li}
        </ul>
    {/if}
{/foreach}
</nav>


