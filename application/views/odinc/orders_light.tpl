{foreach from=$orders item=o}
{if ($o->pay_type eq Model_Order::PAY_CARD) and not $o->in1c}
{assign var=payment value=$o->payment()}
{$o->id}©ОПЛАТА©{if not $o->can_pay}НЕРАЗРЕШЕНА{elseif empty($payment) or $payment->status lt Model_Payment::STATUS_Authorized}РАЗРЕШЕНА{else}{$payment->status}{/if}©{$payment->status_time|regex_replace:'~(20(\d\d))-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)~':'$4.$3.$2,$5:$6:$7'}©{$o->pay8}©{*$payment->id|default:''*}
{/if}{if ($o->delivery_type eq Model_Order::SHIP_OZON) and not $o->in1c}
{$o->id}©ОЗОН©{$o->data->ozon_barcode}©{$o->data->ozon_status}
{/if}
{/foreach}
КОНЕЦ ФАЙЛА
