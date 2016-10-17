{foreach $routes as $route}
МАРШРУТ: {$route->id}@{$route->resource_id}@{$route->date}@{$route->start}@{$route->finish}@{$route->distance}
{if not empty($route_orders[$route->id])}
{foreach $route_orders[$route->id] as $ro}
{$order = $orders[$ro]}
ЗАКАЗ: {$order->id}@{$order->route_number}@{$order->order_code}@{$order->arrival}@{$order->departure}@
{foreachelse}
НЕТ ЗАКАЗОВ.
{/foreach}
{/if}
{foreachelse}
НЕТ МАРШРУТОВ.
{/foreach}