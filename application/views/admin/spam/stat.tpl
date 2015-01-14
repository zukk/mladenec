<h1>Статистика количества адресов в доменах</h1>
<p>Всего {$total_domains} доменов, и {$total_mails} адресов</p>
<table>
    <tr>
        <th>Домен</th>
        <th>Количество</th>
        <th>%</th>
    </tr>
{foreach $domains as $d => $cnt}
    {if $cnt lte 3}{continue}{/if}
    <tr>
        <td>{$d}</td>
        <td>{$cnt}</td>
        <td>{round(($cnt / $total_mails) * 100,2)} %</td>
    </tr>
{/foreach}
</table>
<p>Домены с количеством адресов менее 4 не выводятся.</p>