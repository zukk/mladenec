<!DOCTYPE html>
<html>
    <head>
        
    </head>
    <body>        
        <form action="" enctype="multipart/form-data" method="post">
            <input type="hidden" name="mdir_id" value="{$mdir_id|default:0}" />
            <input type="file" name="files[]" multiple="multiple" value="Загрузить файлы">
            <input type="submit" class="btn" value="Загрузить" />
        </form>
        {if not is_null($reload)}
            <script type="text/javascript">parent.$.fn.filemanager('load', {$reload});</script>
        {/if}
    </body>
</html>
    