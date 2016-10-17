{if $i->poly or not $i->id}
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
<script>
    function map_init() {
        var poly = [{if not $i->id}[55.78756095,37.40649414],[55.85559523,37.64819336],[55.70545185,37.66467285],[55.78756095,37.40649414]{else}{$i->poly|for_map}{/if}], min = [180, 180], max = [0, 0];

        for (var i in poly) {
            min[0] = Math.min(min[0], poly[i][0]);
            min[1] = Math.min(min[1], poly[i][1]);
            max[0] = Math.max(max[0], poly[i][0]);
            max[1] = Math.max(max[1], poly[i][1]);
        }

        var myMap = new ymaps.Map("map", ymaps.util.bounds.getCenterAndZoom([min, max],[800, 600])),
            polygon = new ymaps.GeoObject({
                geometry: {
                    type: "Polygon",
                    coordinates: [poly]
                }
            });

        myMap.geoObjects.add(polygon);
        polygon.editor.startDrawing();

        polygon.events.add("geometrychange", function () {
            $('#flag').show();
            $('#poly').val(stringify(polygon.geometry.getCoordinates()));
        });
        myMap.controls.add('zoomControl');

        // координаты в строку
        function stringify (coords) {
            var res = '';
            if ($.isArray(coords)) {
                res = [];
                for (var i in coords[0]) {
                    res.push(coords[0][i][1].toPrecision(10) + ' ' + coords[0][i][0].toPrecision(10));
                }
                res = res.join(',');

            } else if (typeof coords == 'number') {
                res = coords.toPrecision(8);
            } else if (coords.toString) {
                res = coords.toString();
            }

            return res;
        }
    }

    ymaps.ready(function() {
        map_init();
    });
</script>
{/if}

<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1 class="unit-80">#{$i->id} {$i->name}</h1>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''|escape:'html'}" class="width-25" />
        <small>это название увидят пользователи, чей адрес попадёт в зону</small>
    </p>
    <p>
        <label for="color">Цвет</label>
        <input type="text" id="color" name="color" value="{$i->color|default:'ffffff'|escape:'html'}" class="width-25" />
        <small>цвет зоны на карте</small>
    </p>
    {if $i->id}
    <p>
        <label>Интервалы зоны</label>
        <a href="{Route::url('admin_list', ['model' => 'zone_time'])}?zone_id={$i->id}">Интервалы зоны</a>
    </p>
    {/if}
    <p>
        <label>Вид на карте</label>
        <div class="area hi">
            <div id="map" style="width:800px; height:600px"></div>
            <small>Вы можете изменить форму зоны, двигая точки на карте</small>
        </div>
	    <input type="hidden" id="poly" name="poly" value="{$i->poly}" />
    </p>
    <p>
        <label for="short">Кратко тарифы зоны</label>
        <textarea id="short" name="short" cols="80" rows="5" class="html">{$i->short}</textarea>
    </p>
	<p>
		<label for="text">Описание зоны (дополнительные условия)</label>
		<textarea id="text" name="text" cols="80" rows="10" class="html">{$i->text}</textarea>
	</p>

    <p>
        <label for="name">Приоритет</label>
        <input type="text" id="priority" name="priority" value="{$i->priority|default:''|escape:'html'}" class="width-25" />
        <small>чем больше, тем раньше проверяется зона</small>
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
    </p>
    <p>
        <a class="no" style="display:none;" id="flag">Вы изменили карту зоны!</a>
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
</form>