<h2>Теги для акций</h2>

<form action="" class="forms forms-inline" method="post">
    <fieldset>
        <legend>Добавление тегов</legend>

        <div class="units-row">
            <div class="unit-25">
                <b>Название</b><br />
                <div>
                    <input type="text" name="title" class="width-100" value="">
                </div>

                <b>УРЛ</b><br />
                <div>
                    <input type="text" name="url" class="width-100" value="">
                </div>

                <b>Порядок</b><br />
                <div>
                    <input type="text" name="order" class="width-100" value="">
                </div>
                <div style='float: right'>
                    <input class='btn' type='submit' value='Добавить' />
                </div>
            </div>
        </div>
    </fieldset>
</form>

<table id="list">
    <tr>
        <th>#</th>
        <th>Название</th>
        <th>URL</th>
        <th>Порядок</th>
        <th>&nbsp;</th>
    </tr>
    {foreach from=$actiontag item=i}
        <tr>
            <td>{$i->id}</td>
            <td>{$i->title}</td>
            <td>{$i->url}</td>
            <td>{$i->order}</td>
            <td>
                <a href="{Route::url("admin_edit", ["model" => "actiontag", "id" => $i->id])}"
                   class="edit_action_tag">&#9998;
                </a>
                &nbsp;&nbsp;
                <a href='javascript:void(0)' class="del_action_tag" onclick="delActionTag({$i->id}); return false">&#10006;</a>
            </td>
        </tr>
    {/foreach}
</table>
{$pager->html('Теги')}

<script>
    function delActionTag(id){
        if( confirm('Действительно удалить тег?') ){
            $.post('/od-men/actiontag/'+id+'/del',{
                id: id
            }, function(data){
                $("#list").html($('#list', data));
            });
        }
    }
</script>