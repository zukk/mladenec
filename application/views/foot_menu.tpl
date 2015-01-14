<div id="foot">
{if $vitrina eq 'kotdog'}
    {foreach from=$menu name=m item=m}
        <div{if $smarty.foreach.m.iteration eq 4} class="cl"{/if}><strong><a href="/{$m.link}">{$m.name}</a></strong>
            {if not empty($m.children)}
                <ul>
                    {foreach from=$m.children item=c}
                        <li><a href="/{$c.link}">-&thinsp;{$c.name}</a></li>
                    {/foreach}
                </ul>
            {/if}
        </div>
    {/foreach}
        <div id="promotop">
            <a href="http://www.facebook.com/mladenec.ru" class="fb" title="Мы в facebook" target="_blank">Мы в facebook</a>
            <a href="http://vk.com/mladenecshop" class="vk" title="Мы в контакте" target="_blank">Мы в контакте</a>
            <a href="http://twitter.com/mladenecshop" class="tw" title="Наш твиттер" target="_blank">Наш твиттер</a>
            <a href="http://www.odnoklassniki.ru/group/55719798046774" class="ok" title="Мы в одноклассниках" target="_blank">Мы в одноклассниках</a>
        </div>

{else}
    <table>
    <tr>
    {foreach from=$menu item=m}
        <td><strong><a href="/{$m.link}">{$m.name}</a></strong>
        {if !empty($m.children)}
            <ul>
            {foreach from=$m.children item=c}
            <li><a href="/{$c.link}">&sdot; &thinsp; {$c.name}</a></li>
            {/foreach}
            </ul>
        {/if}
        </td>
    {/foreach}
    </tr>
    </table>
{/if}
</div>
