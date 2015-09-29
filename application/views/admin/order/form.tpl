<h1>Заказ #{$i->id}</h1>

{if $i->user_id}
<div class="row">
    <div class="half">
        Клиент: <a href="{Route::url('admin_edit',['model'=>'user','id'=>$i->user_id])}">{$i->user->name}</a>
    </div>
    <div class="half">
        <a class="btn" href="{Route::url('admin_list',['model'=>'order'])}?user_id={$i->user_id}">все заказы клиента</a>
    </div>
</div>
{/if}

{include file="user/order/view.tpl" o=$i od=$i->data order_goods=$i->get_goods()}