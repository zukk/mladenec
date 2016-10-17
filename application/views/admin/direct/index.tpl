<h1>Директ - проверка объявлений.</h1>
{if $uploaded}<p>Файл загружен, размер:{$size}, {$height} строк.</p>{/if}
<form method="post" action="" enctype="multipart/form-data" class="forms forms-columnar">
    <p>
        <label for="excel">Загрузите файл</label>
        <input type="file" name="excel" />
    </p>
    <div class="units-row">
        <div class="unit-80">
            <input name="check" value="Проверить объявления" type="submit" class="btn btn-green" />
        </div>
    </div>
</form>
{if not empty($result)}
    <table>
        <tr>
            <th>N</th>
            <th>МО</th>
            <th>Химки</th>
            <th>Щелково</th>
            <th>Юбилейный</th>
            <th>Пушкино</th>
            <th>Мытищи</th>
            <th>Москва</th>
            <th>Королев</th>
            <th>Ивантеевка</th>
            <th>Ссылка</th>
            <th>Надо</th>
            <th><s>Было</s></th>
        </tr>
        {foreach $result as $row}
            {if $row['change']|default:FALSE}
                <tr>
                    <td>{$row['number']}</td>
                    <td>{$row['mo']}</td>
                    <td>{$row['khimki']}</td>
                    <td>{$row['schelkovo']}</td>
                    <td>{$row['ubileiny']}</td>
                    <td>{$row['pushkino']}</td>
                    <td>{$row['mytischi']}</td>
                    <td>{$row['moscow']}</td>
                    <td>{$row['korolev']}</td>
                    <td>{$row['ivanteevka']}</td>
                    <td><a href="{$row['link']}" target='_blank'>#{$row['good_id']}</a></td>
                    <td>{if ! empty($row['ok'])}<span class="green">Активно</span>{else}<span class="red">Остановлено</span>{/if}</td>
                    <td>{$row['active']}</td>
                </tr>
            {/if}
        {/foreach}
    </table>
{/if}