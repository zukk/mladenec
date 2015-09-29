{strip}
{foreach from=$action item=icon key=action_id}
    <abbr class="q" abbr="<b>{$icon.name|escape:html}</b><br />{$icon.preview|escape:html}">
        <a href="{Route::url('action', ['id' => $action_id])}">
            <img {if $icon.type eq 'gift'}src="/i/action/good_icon/2.png" alt="Подарок"{else}src="/i/action/good_icon/1.png" alt="Скидка"{/if} />
        </a>
    </abbr>
{/foreach}
{/strip}