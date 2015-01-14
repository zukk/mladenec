<div id="searchresults">
  {if ! empty($actions)}
    <span class="category">Акции</span>
    {foreach from=$actions item=action}
      {assign var=link value=$action->get_link(0)}
      <a href="{$link}">
        <span class="searchheading">{$action->name|truncate:50:'..':true:true}</span>
        <span>{$action->preview|strip_tags|truncate:200:'..':true}</span>
      </a>
    {/foreach}
  {/if}

  {if ! empty($goods)}
    <span class="category">Найденные товары</span>
    {foreach from=$goods item=g name=g}
      {capture assign=name}{$g->group_name|escape:'html'} {$g->name|escape:'html'}{/capture}
      {assign var=link value=$g->get_link(0)}
      <a href="{$link}">
        <!--img src="{$g->prop->get_img(70)}" alt="{$name}" /-->
        <!--span class="searchheading">{$name|truncate:30:'..':true:true}</span-->
        <!--span>{$name}</span-->
        {$name}
      </a>
    {/foreach}
  {/if}

  {if ! empty($brands)}
    <span class="category">Найденные бренды и разделы</span>
    <div class="item">
    {foreach from=$brands item=brand}
      <span class="searchheading">{$brand.name}</span>
      <ul class="sectionslist">
        {foreach from=$brand.sections item=section}
        <li><a style="display: inline" href="{Route::url('section',['translit' => $section.translit, 'id' => $section.id])}?b={$brand.id}">{$section.name}</a></li>
        {/foreach}
      </ul>
    {/foreach}
    </div>
  {/if}

  <span class="seperator footer">
    <a href="/site_map/list.php" title="Карта товаров">Хотите продолжить поиск? <br /><span class="decoration">Кликните, чтобы продолжить поиск по карте товаров</span></a>
  </span>
  <br class="break" />
</div>