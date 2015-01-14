<h1>Выгрузка в Ozon</h1>
<p>Просмотреть товары, <a href="/od-men/good?ozon=1">отмеченные к выгрузке</a>.</p>
<form action="" method="post">
    <div class="dialog_layout">
        <div class="dialog_label">Сформированные файлы:</div>
        <div class="dialog_field">
            {if not empty($files)}
                {foreach $files item=file}
                    <div><input type="checkbox" name="xml_file[{$file}]" />&nbsp;<a target="_blank" title="Файл откроется в новом окне" href="/xml/ozon/{$file}">{$file}</a></div>
                {/foreach}
            {/if}
        </div>
        <div class="dialog_label">С отмеченными:</div>
        <div class="dialog_field">
            <select id="file_action" name="file_action">
                <option value="skip">Выберите действие</option>
                <option value="delete">Удалить</option>
            </select>
        </div>
        <div class="dialog_label">&nbsp;</div>
        <div class="dialog_field">
            <input type="checkbox" id="make_file" name="make_file" />&nbsp;<label for="make_file">Сформировать файл</label>
        </div>
        <div class="dialog_label">&nbsp;</div>
        <div class="dialog_field">
            <input type="submit" class="btn ok" name="submit" value="Выполнить" />
        </div>
    </div>
</form>