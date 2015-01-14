<input type="hidden" id="filemanager-current-dir-id" value="{$current_dir_id}" />
<div class="units-row">
    <div class="unit-100">
        <div class="filemanager-pathway">
            {if $current_dir_id neq 0}
                <span data-filemanager-load="0">В начало</span>
            {/if}
            {foreach $pathway as $p_el}
                <span data-filemanager-load="{$p_el['id']}">{$p_el['name']}</span>
            {/foreach}
        </div>
    </div>
</div>
<div class="units-row">
    <div class="unit-30">
            <input type="text" class="unput-search" size="30" /><input type="button" class="btn" value="Создать" />
    </div>
    <div class="unit-30">
            <iframe src="/od-men/ajax/filemanager_upload.php?mdir_id={$current_dir_id}" height="43" width="400"></iframe>
    </div>
    <div class="unit-40">
            {if  $order_by eq 'id'}
                {$order_by_other = 'name'}
                {$order_by_other_name = 'названию'}
            {else}
                {$order_by_other = 'id'}
                {$order_by_other_name = 'дате'}
            {/if}
            {if  $order_dir eq 'ASC'}
                {$order_dir_other = 'DESC'}
                {$order_dir_other_name = '&uArr;'}
            {else}
                {$order_dir_other = 'ASC'}
                {$order_dir_other_name = '&dArr;'}
            {/if}
            <input type="button" onclick="$.fn.filemanager('load', {$current_dir_id})" class="btn" value="Обновить"/>
            <input type="button" onclick="$.fn.filemanager('load', {$current_dir_id}, '{$order_by_other}', '{$order_dir}')" class="btn" value="Сортировка по {$order_by_other_name}"/>
            <input type="button" onclick="$.fn.filemanager('load', {$current_dir_id}, '{$order_by}', '{$order_dir_other}')" class="btn" value="{$order_dir_other_name}"/>
    </div>
</div>
<div class="units-row">
    <div class="unit-30">
        <ul class="filemanager-folders">
            {foreach $directories as $dir}
                <li><span data-filemanager-load="{$dir->id}">{$dir->name}</span></li>
            {/foreach}
        </ul>
    </div>
    <div class="unit-70">
        <ul class="blocks-6 filemanager-files">
            {foreach $files as $file}
                <li filemanager-file-path="{$file->get_link(false)}">
                    <div><img src="/{$file->get_thumb(FALSE)}" title="{$file->name}, создан {date('d-m-Y H:m',$file->created_ts)}" alt="{$file->name}" /></div>
                    {$file->name}
                </li>
                {foreachelse}
                <p>Файлы не загружены</p>
            {/foreach}
        </ul>
    </div>
</div>