<h1>Создание теговых страниц из ссылок</h1>
<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <table>
        <tr>
            <th>Создать</th>
            <th>Сущ</th>
            <th>Ссылка</th>
            <th>Название</th>
            <th>Транслит /catalog/ ... </th>
            <th>Разделы</th>
            <th>Бренды</th>
            <th>Фильтры</th>
        </tr>
    {foreach $links|default:[] as $n=>$l}
        <tr>
            <td><input type="checkbox" name="tags[{$n}][go]" value="1" {if empty($l['tags'])}checked="checked" {/if}></td>
            <td>{implode(', ', $l['tags']|default:[])}</td>
            <td><a href="/{$l['txt']}" target="_blank">{$l['txt']|truncate:20}</a></td>
            <td><input type="text" size="35" name="tags[{$n}][title]" value="{$l['title']}" />
            <td><input type="text" size="35" name="tags[{$n}][translit]" value="{$l['translit']}" />
                {if not empty($l['duplicate_code'])}<br /><span class='b red'>!!!!! ДУБЛЬ !!!!</span>{/if}
            </td>
            <td><input type="text" size="15" name="tags[{$n}][sections]" value="{implode(', ', $l['c']|default:[])}" /></td>
            <td><input type="text" size="15" name="tags[{$n}][brands]" value="{implode(', ', $l['b']|default:[])}" /></td>
            <td>{foreach $l['f']|default:[] as $fk => $fv}
                {$fk}: <input type="text" size="45" name="tags[{$n}][filters][{$fk}]" value="{implode(', ', $fv)}" /><br />
                {/foreach}
            </td>
        </tr>
    {/foreach}
    </table>
    {$tags = $smarty.post['tags']|default:[]}

    <p>
        <label for="links">Ссылки </label>
        <textarea name="links" rows="20" cols="90">{$links_txt}</textarea>
        <span class="desc">Список ссылок, по 1 ссылке в строке.</span>
    </p>
        <p class="forms-inline">
            <input name="parse" value="Разобрать ссылки" title="Все изменения, сделанные в списке параметров теговых, будут потеряны!" type="submit" class="btn ok"/>
            {if not empty($links)}
                <input name="generate" value="Сгенерировать теговые" type="submit" class="btn btn-green"/>
            {/if}
    </p>
</form>