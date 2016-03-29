{if Kohana::$server_name == 'mladenec'}

    <div id="side">
        {$menu}
        <a href="/catalog" id="ctg">Карта товарного каталога</a>
    </div>
    <div id="content">
        {$body}
    </div>

{elseif Kohana::$server_name == 'ogurchik'}

    {assign var=bread value=''}
    {assign var=h1 value=''}

    {if preg_match('~.*(<div id="bread.*?/div>).*~isu', $body, $matches)}
        {assign var=bread value=$matches[1]}
        {assign var=body value=$body|replace:$bread:''}
    {/if}

    {if preg_match_all('~.*(<h1.*/h1>).*~isuU', $body, $matches)}
        {assign var=h1 value=$matches[1][0]}
        {assign var=body value=$body|replace:$h1:''}
    {/if}

    {$bread}
    {$h1}

    <div id="good_good">
        <div id="side">{$menu|default:''}</div>

        <div id="content">
        {$body|default:'Eatmart.ru'}
        </div>
        <div class="cl"></div>

    </div>

{/if}