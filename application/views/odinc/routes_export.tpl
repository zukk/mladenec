{*foreach from=$routes item=r}
МАРШРУТ
НОМЕР:{$r->id}
РЕСУРС:{$r->resource_id}
ДАТА:{$r->date|ru_date}
СТАРТ:{$r->start}
ФИНИШ:{$r->finish}
РАССТОЯНИЕ:{$r->distance}
{foreach from=$o->get_items() item=item}
{$item->id}@{$item->point->latitude}@{$item->point->longitude}@{$item->point->address}@{$item->arrival}@{$item->departure}
{/foreach}
КОНЕЦ МАРШРУТА
{/foreach*}
КОНЕЦ ФАЙЛА