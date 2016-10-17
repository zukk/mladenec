{foreach $orders as $order}
{$order->id}@{$order->order_code}@{$order->check_number}@{$order->order_id}@{$order->status}@{$order->resource_id}
{foreachelse}
НЕТ ЗАКАЗОВ
{/foreach}