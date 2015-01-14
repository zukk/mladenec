<div id="maina">

  <div class="clear mt"></div>

  <div class="na"  style="width:200px">
    <div id="last_comment" style="margin-top:0">
      <a class="big" href="{Model_Comment::get_list_link()}">Отзывы</a>

      {foreach from=Model_Comment::last(2) item=c}
        <p class="comment">
          <a href="{$c->get_link(0)}">{$c->user_name}</a><i></i>
          <strong>{$c->name}</strong>
          {$c->text|truncate:100}
          <a href="{$c->get_link(0)}" class="l">Ответ магазина<i></i></a>
        </p>
        <small>{$c->get_answer_by($c->answer_by)}</small>
      {/foreach}
    </div>
  </div>

  <div class="na" style="width:360px; margin-right:25px;">
    <a class="big" href="{Model_New::get_list_link()}">Новости</a>
    <ul>
      {foreach from=$news item=n}
        <li>
          <small>{$n->date|date_ru}</small>
          {$n->get_link()}
          <a href="{$n->get_link(0)}">{$n->image->get_img()}</a>
          <p>{$n->preview}</p>
        </li>
      {/foreach}
    </ul>
  </div>

  <div class="na" style="width:340px;" >
    <a class="big" href="{Route::url('action_list')}">Акции</a>
    <ul class="act">
      <li>
        <img class="fr" src="/i/action_star25.png" alt="Акция с подарками" width="25" />
        <a href="{Route::url('action_current_list')}">{$config->actions_header|default:'Акции месяца'}</a>
        {$config->actions_subheader|nl2br}
      </li>
      {foreach from=$actions item=n}
        <li>
          {if $n->type == 1}<img class="fr" src="/i/podarok.png" alt="Акция с подарками" width="25" />{/if}
          {if $n->type == 2}<img class="fr" src="/i/sale.png" alt="Акция со скидкой" width="25" />{/if}
          {$n->get_link()}
          {$n->preview|nl2br}
        </li>
      {/foreach}
    </ul>
  </div>

</div>
