{assign var=rand value=rand(1,100000)}
{if not empty($good->quantity)}
	{assign var=q value=$good->quantity}
{/if}
{if not empty($can_buy)}
	{assign var=id value=$can_buy}
	{assign var=q value=$good}
{else}
	{assign var=id value=$good->id}
{/if}

<div id='buy-{$rand}'>
	<input id="qty_{$id}" name="qty[{$id}]" value="{if not empty($active)}1{else}{$q|default:0}{/if}" oldval="{$q|default:0}" price="{$good->price|default:'1'}"/>
	<select class="small" style="display: none;">
		<option value="1">РЁС‚СѓРє</option>
	</select>
</div>
<script>  
	$(function(){
		var working = false;
        var lastHash = false;
        var queryTimeout = false;
        var loaderTimeout = false;
		var loader = new Image();
		loader.src="/i/load.gif";
		loader = $(loader);
		
		var process = function(el, mode){
			
			// if( working ) return false;
			
			working = true;
			var allReload = false;
			
            {literal}
			var params = {};
			params[mode] = $(el).attr('id').substring(4);
            {/literal}
			
			if( mode == 'change' ){
				params['value'] = $(el).val();
				if( params['value'] < 1 ){
					allReload = true;
				}
			}
			else{
				if( $("[name='qty["+params[mode]+"]']").val() < 1 ){
					allReload = true;
				}
			}
			
			if( allReload && ( params[mode] != 'blago' || $("[name='qty["+params[mode]+"]']").val() < 0 ) ){
				$("[name='qty["+params[mode]+"]']").val("1");
				params = {
					value: 1,
					change: params[mode]
				};
				mode = 'change';
			}
			
            lastHash = params.hash = generateUUID();
            
            if (queryTimeout) {
                clearTimeout(queryTimeout);
                if (loaderTimeout) clearTimeout(loaderTimeout);
            }
            
			loaderTimeout = setTimeout(function(){
				loader.css({
					display: 'block',
					position: 'absolute',
					top: '50%',
					left: '50%',
					'margin-left': '-12px',
					'margin-top': '-12px'
				}).insertAfter($(el));
			},1400);
            
            queryTimeout = setTimeout(function(){
            
                $.ajax({
                    url: '/personal/cart_recount.php',
                    dataType: 'JSON',
                    method: 'POST',
                    data: params,
                    success: function(data){

                        working = false;
                        queryTimeout = false;
                        clearTimeout(loaderTimeout);
                        
                        if (data.hash == lastHash) {

                            loader.remove();

                            $('.cart-header').empty().append(data.header);
                            if ($('#cart-delivery').hasClass('opened')) $('#cart-delivery').empty().append(data.delivery);

                            $.map(data.goods, function(v, id){
                                if (data.no_possible[id])
                                {
                                    $("[name='qty["+id+"]']").val(v);
                                }
                                $('#cart-good-tr-'+id+' .price').html(format_price( +data.prices[id] ));
                                $('#cart-good-tr-'+id+' .total').html(format_price( +data.totals[id] ));
                            });
                            $('.cart-all-summ span').html(format_price( +data.total ));
                            if (data.discount) {
                                $('.cart-prm .discount').html(format_price( +data.discount ));
                            }
                            
                            $(".cart-gift-row").detach();
                            if (data.presents_html) {
                                $('#cart_goods > tbody').append(data.presents_html);
                                $('.cart-gift-radio').mladenecradio();
                            }
                        }
                    }
                });
            },500);
			return false;
		};
		
		$('#buy-{$rand} > input:text').incdec({
			onInc: function(el){
				$(el).val(+$(el).val()+1);
				return process(el, 'change');
			},
			onDec: function(el){
                var val = +$(el).val();
                
                if (val > 0) $(el).val(val-1);
                
				return process(el, 'change');
			},
			onChange: function(el){
				return process(el, 'change');
			}
		});
	});
</script>
