<h1>{$module_name}</h1>
<script type="text/javascript">
var directoriesList = [{foreach from=$directories_array item=da}
    {
        id:'{$da['id']}',
        name:'{$da['name']}'
    },
{/foreach}];
$(document).ready(function() {
    $('div.fm-pathway-el ul').hide();
    $('div.fm-pathway-el > a + span').click(function(e){
        $(this).next().toggle();
        e.preventDefault();
    });
    $('#fm-folders li b').click(function(){
        
        $('#fm-folders li form').hide();
        
        var el = $(this).parent().children('a.dir');
        
        var id = el.data('id');
        var name = el.text();
        var comment = el.prop('title');
        var parent = $(this).parent();
        
        if(parent.children().is('form')) {
            parent.children('form').show();
        } else {
            var html = '';
            html  += '<form action="" method="post">';
            html += '<select name="parent_id">';
            html += '<option value="-1">Не переносить</option>';
            html += '<option value="0">/</option>';
            for(var i in directoriesList) {
            html += '<option value="' + directoriesList[i].id + '">' + directoriesList[i].name + '</option>';
            }
            html += '</select>';
            html += '<input type="hidden" name="id" value="' + id + '" />';
            html += '<input type="text" name="name" value="' + name + '" />';
            html += '<textarea name="comment">' + comment + '</textarea>';
            html += '<input type="submit" class="btn btn-round" name="savedir" value="Сохранить" />';
            html += '<input type="button" class="btn btn-round" name="dir_edit" value="Отмена" />';
            html += '</form>';
            parent.append(html);
            parent.find('input[type="button"]').click(function(){
                $('#fm-folders li form').hide();
            });
        }
    });
    $('#fm-files li').each(function() { var id = $(this).data('id'); });
    $('#fm-files li').mouseenter(function(){
        $(this).children(' div.overlay').show();
    
    });
    $('#fm-files li').mouseleave(function(){
        $(this).children(' div.overlay').hide();
    
    });
    $('div.overlay').hide();
});

</script>

<style type="text/css">
    div.fm-pathway-el {
        float:left;
        margin:0.3em;
        padding:1px;
        position:relative;
    }
    div.fm-pathway-el a{
        display:block;
        float:left;
        margin:0.8em 0.2em;
        padding:1px 1px 1px 1px;
        
    }
    div.fm-pathway-el span{
        display:block;
        float:left;
        margin:0.7em 0.1em;
        padding:1px;
        width:17px;
        height:17px;
        background-image:url('/i/fm-folder.png');
        background-repeat: no-repeat;
        background-position:center;
    }
    div.fm-pathway-el ul{
        position:absolute;
        top:2.5em;
        clear:left;
        margin:0;
        padding:0.4em 0.2em;
        border:1px solid #d7d7d7;
        list-style-type:none;
        max-height:10em;
        overflow-y:auto;
        background-color:#fafafa;
        width:300px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }
    div.fm-pathway-el ul a{
        float:none;
        margin:0.1em;
    }
    div.fm-pathway-el form {
        /* border:1px solid #d7d7d7; */
        background-color: #eaeaea;
        padding:0.5em;
        border-radius:5px;
    }
    div.fm-pathway-el > a + span {
        padding:0.2em;
        
        cursor:pointer;
        border:1px solid lightgray;
        border-radius:3px;
    }
    #fm-folders {
        float:left;
        width:24em;
    }
    #fm-folders ul {
        list-style:none;
    }
    #fm-folders li {
        
        margin:0.5em;
        padding:3px;
        overflow:hidden;
    }
    #fm-folders li:hover {
        background-color:#eaeaea;
        border-radius:5px;
    }
    #fm-folders li a{
        display:block;
        float:left;
        padding-left:20px;
        margin-right:1em;
        background-image:url('/i/fm-folder.png');
        background-repeat: no-repeat;
        background-position: left center;
        width:13em;
    }
    #fm-folders li b{
        background-image:url('/i/fm-edit.png');
    }
    #fm-folders li a.delete{
        background-image:url('/i/fm-delete.png');
    }
    #fm-folders li b,#fm-folders li a.delete{
        display:none;
        float:left;
        height:17px;
        width:17px;
        border:1px solid #eaeaea;
        background-color:#eaeaea;
        background-repeat: no-repeat;
        background-position: left center;
        cursor:pointer;
    }
    #fm-folders li:hover b, #fm-folders li:hover a.delete{
        display:block;
    }
    
    #fm-folders li form {
        padding:0.2em;
        clear:left;
    }
    #fm-folders form input,#fm-folders form textarea,#fm-folders form select{
        margin:0.5em;
    }
    #fm-folders form input[type="text"],#fm-folders form textarea,#fm-folders form select {
        width:95%;
        display:block;
    }
    #fm-folders form textarea {
        height:5em;
    }
    #fm-files {
        float:left;
    }
    ul#fm-files  {
        list-style-type:none;
        margin: 0.5em;
        max-width:70%;
    }
    #fm-files li {
        position:relative;
        float:left;
        margin:0.3em;
        padding:0.4em;
        width:120px;
        height:150px;
        font-size:10pt;
        text-align:center;
        border:1px solid #eaeaea;
        border-radius:5px;
    }
    #fm-files li p {
        margin:0 0.2em 0.3em 0.2em;
        overflow:hidden;
    }
    #fm-files div.fm-thumb-wrapper {
        height:100px;
    }
    div.overlay {
        color:red;
        position:absolute;
        top:0;
        left:0;
        width:115px;
        padding:8px;
        border-radius:5px 5px 0 0;
        background-image: url('/i/filemanager/z60.png');
        text-align:right;
    }
.filemanager-pathway select  {
    padding:0.2em;
    border:1px solid lightgray;
   }
</style>
<div id="filemanager">
<div class="fm-pathway">
    <div class="fm-pathway-el">
        <a href="{Route::url('admin_filemanager_dir',['mdir_id'=>0])}">/</a><span></span>
        <ul>
            {foreach from=$root_subdirs item=rs}
                <li>{$rs->get_link_admin()}</li>
            {/foreach}
        </ul>
    </div>
    {if ! empty($pathway)}
        {foreach from=$pathway item=pw}
            {assign var="pw_childs" value=$pw->get_children()}
            <div class="fm-pathway-el">
                {$pw->get_link_admin()}
                {if ! empty($pw_childs)}
                    <span></span>
                    <ul>
                        {foreach from=$pw_childs item=pwc}
                            <li>{$pwc->get_link_admin()}</li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
        {/foreach}
    {/if}
    <div  class="fm-pathway-el">
        <form action="{Route::url('admin_filemanager_dir',['mdir_id'=>$mdir_id])}" method="post">
            <input type="hidden" name="parent_id" value="{$mdir_id}" />
            <input type="text" name="name" />
            <input type="submit"  class="btn btn-square" name="savedir" value="Создать директорию" />
        </form>
    </div>
    <div class="fm-pathway-el">
            <form action="{Route::url('admin_filemanager_dir',['mdir_id'=>$mdir_id])}" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mdir_id" value="{$mdir_id}" />
                <input type="file" name="files[]" multiple="multiple" value="Загрузить файл" />
                <input type="submit"  class="btn btn-square" name="upload" value="Загрузить" />
            </form>
    </div>
</div>
<div class="cb"></div>
    {if ! empty($subdirs)}
        <div id="fm-folders">
            <ul>
                {foreach from=$subdirs item=dir}
                    <li><a class="dir" href="{Route::url('admin_filemanager_dir',['mdir_id'=>$dir->id])}" data-id="{$dir->id}" title="{$dir->comment}">{$dir->name}</a><b></b>
                        {if ! ($dir->childs_count gt 0) AND ! ($dir->files_count gt 0)}
                            <a class="delete" href="{Route::url('admin_filemanager_dir',['mdir_id'=>$dir->parent_id])}?del={$dir->id}"></a>
                        {/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
    <ul id="fm-files">
        {foreach from=$files item=file}
            <li data-id="{$file->id}">
                <div class="fm-thumb-wrapper">{$file->get_thumb( TRUE )}</div>
                <p>
                    {if mb_strlen($file->name) lt 32}
                        {$file->get_link()}
                    {else}
                        <a href="{$file->get_link( FALSE )}" title="{$file->name}" target="_blank">{mb_substr($file->name,0,26,'UTF-8')}</a>
                    {/if}
                </p>
                <div class="overlay"><i></i><a href="{Route::url('admin_del',['model'=>'mfile','id'=>$file->id])}?return_url={urlencode({Route::url('admin_filemanager_dir',['mdir_id'=>$mdir_id])})}"  onclick="return confirm('Файл может использоваться на сайте. Удалить насовсем?')"><img src="/i/fm-delete.png" /></a></div>
            </li>
        {/foreach}
    </ul>
</div>