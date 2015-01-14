<h1>Наборы товаров</h1>
<form rel="ajax" method="post" action="{Route::url('admin_ajax_list',['model'=>'set'])}" class="ajax" enctype="multipart/form-data">
    <div class="units-row">
        <div class="unit-50"><a href="{Route::url('admin_ajax_add',['model'=>'set'])}" rel="ajax" data-fancybox-type="ajax" class="green btn btn-round">Создать набор</a></div>
        <div class="unit-50">
            <input type="text" name="search" value="{$search}" />
            <input type="submit" name="find" class="btn" value="Найти" />
        </div>
    </div>
</form>
{if not empty($search)}<p><a  rel="ajax" data-fancybox-type="ajax" href="{Route::url('admin_ajax_list',['model'=>'set'])}">Вернуться к списку</a></p>{/if}
{$pager->html('Наборы', FALSE, TRUE)}
<form action="">
    <table id="list">
        <tr>
            <th>id</th>
            <th>название</th>
            <th>товаров</th>
            <th></th>
        </tr>
        {foreach from=$list item=i}
            {$set_url = Route::url('admin_ajax_form',['model'=>'set','id'=>$i->id])}
            <tr {cycle values='class="odd",'}>
                <td><small><a rel="ajax" data-fancybox-type="ajax" href="{$set_url}">{$i->id}</a></small></td>
                <td><a rel="ajax" data-fancybox-type="ajax" href="{$set_url}">{$i->name}</a></td>
                <td>{$i->cnt}</td>
                <td>{if $smarty.get.choose|default:FALSE}<input type="button" class="btn btn-round choose" rel="{$i->id}" value="Выбрать" />{/if}</td>
            </tr>
        {/foreach}
    </table>
</form>

<script type="text/javascript">
    $(document).ready(function(){
        $(".choose").click(function(){

            $("#{$smarty.get.choose}").val($(this).attr('rel'));
            $.fancybox.close();
        });
    });
</script>
{$pager->html('Наборы', FALSE, TRUE)}

