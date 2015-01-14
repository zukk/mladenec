{* Доставка транспортной компанией *}
<div class='cart-delivery-companies'>
	{if !empty( $address )}
    {foreach from=$address item=a name=a}
        {capture assign=addr}{$a->city}, {$a->street}, {$a->house}, {$a->kv}{/capture}
         <label title="{$addr}">
			 <input type="radio" rel="{$a->city}" name="address_id_3" value="{$a->id}" {if $smarty.foreach.a.first} checked="checked"{/if} />{$addr}
		 </label>
    {/foreach}
	{/if}
	
    <label>
		<input type="radio" name="address_id_3" value="0" {if empty($address)}checked="checked"{/if}/> Новый адрес
	</label>
</div>
<script>
	$(function(){
		$('.cart-delivery-companies input[type=radio]').mladenecradio({
			onClick:  function(check, value ){
				var d = { };
				{foreach from=$address item=a name=a}
				d[{$a->id}] = {
					city: "{$a->city}",
					street: "{$a->street}",
					house: "{$a->house}",
					enter: "{$a->enter}",
					domofon: "{$a->domofon}",
					floor: "{$a->floor}",
					lift: "{$a->lift}",
					kv: "{$a->kv}",
					latlong: "{$a->latlong}"
				};
				{/foreach}
				var
					tableForm = $('#new_addr');
				if( check && value > "0" ){
					fillReg(d[value]['city']);
					tableForm.find('input').each(function(){
						if( d[value][$(this).attr('name') ] ){
							$(this).val(d[value][$(this).attr('name') ]);
						}
					});
				}
				else if( check && value == "0" ){
					$('#to_reg').html('Не определен');
					tableForm.find('input').val('');
				}
			}
		});
	});
</script>

<div class="mt cl">
    <div class="half {*if not empty($address)}hide{/if*}" id="new_addr">
        <label class="l" for="city">Город</label><input name="city" id="city" class="txt" value="{$address.0->city|default:''}"/>
        <label class="l" for="street">Улица</label><input name="street" id="street" value="{$address.0->street|default:''}" class="txt" />
        <label class="l" for="house">Дом</label><input name="house" id="house" value="{$address.0->house|default:''}" class="txt short" />
        <label class="l" for="kv">Номер квартиры/офиса</label><input name="kv" id="kv" value="{$address.0->kv|default:''}" class="txt short" />
    </div>

    <div>
        <label class="l">Регион:</label><abbr abbr="Заполните город" id="to_reg">Не определён</abbr>
		<br />
        <p>Стоимость доставки зависит от&nbsp;количества товаров в&nbsp;корзине, их&nbsp;веса и&nbsp;объема.</p>
		<br />
        <!--input class="butt small" value="Рассчитать стоимость" type="button" id="count_ship" /-->
    </div>
</div>

<input type="hidden" name="floor" value="1" />
<input type="hidden" name="ship_zone" value="{Model_Zone::ZAMKAD}" />
<input type="hidden" name="latlong" value="TRANSPORT" />

<div id="rz" class="mt cl"></div>

<script language=javascript>

    var cit = [], k = [], r = [];
    {foreach from=ORM::factory('dpd_city')->order_by('name')->find_all()->as_array() item=c}
    cit.push("{$c->name|escape:html}");
    k.push({$c->region_id});
    {/foreach}
    {foreach from=ORM::factory('dpd_region')->order_by('id')->find_all()->as_array() item=r}
    r[{$r->id}] = "{$r->name|escape:html}";
    {/foreach}

    var ToCity1 = '';

    function cr1() {
        $("#rz").empty();

        var e = false;
        if ( ! $('#city').val()) {
            $('#city').removeClass('ok').addClass('error').attr('error', 'Не указан город');
            e = true;
        }
        if (e) return false;

        $("#rz").html('<p>Получение предложений от транспортных компаний <i class="load"></i></p>');

        // другой вариант вызова
        $.post("edost.php", { city:$("#city").val()}, function(rt) {
            $("#rz").html(rt);
            $("#rz input:radio").radio();
        });
    }

    function fillReg(ToCity2) { // Находим код региона по выбранному городу
        ToCity2 = $.trim(ToCity2);
        var c1 = -1;
        for (var i = 0; i < cit.length; i++) { if ( cit[i] == ToCity2) { c1 = k[i]; break;}}
        if (c1 == -1) $("#to_reg").html('Не определён');  else $("#to_reg").html(r[c1]);
        ToCity1 = ToCity2;
    }

    $(document).ready(function() {

        $('#count_ship').click(function() { cr1()});

        $.getScript('/j/jquery.autocomplete.js', function() {
            $("#city").autocomplete({
                lookup: cit,
                deferRequestBy:3,
                minChars:1,
                maxHeight:300,
                onSelect: function(value, data) {
                    fillReg(value);
                }
            });
        });
    });
</script>
