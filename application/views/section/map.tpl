<div id="breadcrumb">       
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span>Карта каталога</span> 
</div>

<h1>Карта каталога</h1>

<div id="catmap">
{foreach from=$map item=s name=s}
    <div{if $smarty.foreach.s.iteration mod 4 eq 1} class="ml0"{/if}>
        <strong>{$s->name|replace:' и ':' и&nbsp;'}</strong>

        {if $s->children}
        <ul>
            {foreach from=$s->children item=ch key=kk}
                <li><a href="{$ch->get_link(0)}">{$ch->name} [{$ch->qty}]</a></li>
            {/foreach}
        </ul>
        {/if}
    </div>
{/foreach}
</div>