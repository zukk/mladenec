

<form method="post" action="" enctype="multipart/form-data" class="forms forms-columnar">
    {if $i->id}
        <h2>Баннер {$i->name}</h2>
    {else}
        <h2>Новый баннер в слайдере</h2>
    {/if}
    <p>
        <label for="name">Название</label>
        <input type="text" name="name" id="name" value="{$i->name}" />
    </p>
    <p>
        <label for="slider_id">Слайдер</label>
        <select name="slider_id">
            {html_options options=$i->sliders() selected=$i->slider_id}
        </select>
    </p>
    <p>
        <label for="action_id">Акция</label>
        <select name="action_id">
            <option value="0">Не привязано</option>
            {html_options options=ORM::factory('action')->where('allowed','=',1)->find_all()->as_array('id','name') selected=$i->action_id}
        </select>
        {$action = ORM::factory('action',$i->action_id)}
        {if $action->loaded()}
            <span class="forms-desc">Привязан к акции: №{$action->id}, {$action->name} 
                {if $action->active}<span class="green">работает</span>
                {else}<span class="red">остановлена</span>
                {/if}
            </span>
        {/if}
    </p>
    <p class="forms-inline">
        <label>Период активности</label>
		с <input type="text" name="from" class="datepicker" value="{$i->from|default:date('Y-m-d H:i')}" />
		по <input type="text" name="to" class="datepicker" value="{$i->to|default:date('Y-m-d H:i', time()+60*60*24*3)}" />
    </p>
	<script>
		$(function(){
			$('.datepicker').datepicker({
				dateFormat: 'yy-mm-dd {date('H:i')}'
			});
		});
	</script>
    <p>
        <label for="url">URL</label>
        <input type="text" name="url" class="width-70" id="url" value="{$i->url}" />
    </p>
    <p>
        <label for="newtab">В&nbsp;новом окне</label>
        <input type="checkbox" id="newtab" name="newtab" value="1" {if $i->newtab}checked="checked"{/if} />
    </p>
    <p class="input-groups">
        <label>Картинка</label>
        <span class="btn-group">
            <input type="text" size="40" name="src" class="input-search" id="src" value="{$i->src}" />
            <span class="btn btn-round" data-filemanager="#src" data-mdir="7">Выбрать</span>
	</span>
    </p>
		<input type="hidden" id="active" name="active" value="{if $i->active}1{else}0{/if}"  />
    <p>
        <label>Активность</label>
		{if $i->active}<span class='label label-green'>да</span>{else}<span class='label label-red'>нет</span>{/if}
    </p>
    <p>
        <label for="allow">Разрешен</label>
        <input type="checkbox" id="allow" name="allow" value="1" {if $i->allow} checked="checked"{/if} />
    </p>
    <p>
        <label for="name">Сортировка</label>
        <input type="text" name="order" id="order" value="{$i->order}" />
    </p>
    <p class="forms-inline">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
    </ul>
    <div>
        <img src="{$i->src}" />
    </div>

</form>

{if $i->id}
    <p><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
{/if}
{literal}
    
<script type="text/javascript">

</script>

<style type="text/css">

</style>
{/literal}