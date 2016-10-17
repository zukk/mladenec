/**
 * 
 * @param string selector
 * @param object params {
 *      containerElId:'name of container for choose dialog',
 *      }
 * @param callback onSuccess
 * @returns null
 */
function chooseGoods(triggerElement,params,onApply) {
    $(triggerElement).click(function(){
        /* Рисуем контейнер */
        if ( ! params['containerElId']) {
            params['containerElId'] = 'choose_goods_container';
        }
        var containerEl = chooseGoodsContainerEl(params['containerElId'],triggerElement);
        $(this).parent().append(containerEl);
        /* \ рисуем контейнер */
        chooseGoodsDraw(params);
        $(triggerElement).unbind('click');
        $(triggerElement).click(function(){
            checkboxes = $('input.choose_goods_checkbox:checked');
            var ids = [];
            checkboxes.each(function(){
                ids.push($(this).val());
            });
            onApply(ids);
        });
    });
}
function chooseGoodsDraw(params) {
    var containerEl = $('#' + params['containerElId']);
    
    if ( ! document.getElementById('choose_goods_params')) {
        
        chooseGoodsParams(containerEl);
    }
    
    var name = $('#choose_goods_name').val();
    if(name.length > 3) { params['name'] = name; }
    else { delete params['name']; }

    var code = $('#choose_goods_code').val();
    if(code.length > 2) {params['code'] = code;}
    else { delete params['code']; }
    
    var brandId = $('#choose_goods_brand_id').val();
    if(brandId) params['brand_id'] = brandId;
    else { delete params['brand_id']; }

    var sectionId = $('#choose_goods_section_id').val();
    if(sectionId) params['section_id'] = sectionId;
    else { delete params['section_id']; }
    
    $('#choose_goods_name').unbind('keyup');
    $('#choose_goods_code').unbind('keyup');
    $('#choose_goods_brand_id').unbind('change');
    $('#choose_goods_section_id').unbind('change');
    $('#choose_goods_name').keyup(function(){
        var valLen = $(this).val();
        if(valLen === 0 || valLen > 3) chooseGoodsDraw(params);
        chooseGoodsDraw(params);
    });
    $('#choose_goods_code').keyup(function(){
        var valLen = $(this).val();
        if(valLen === 0 || valLen > 2) chooseGoodsDraw(params);
    });
    $('#choose_goods_brand_id').change(function(){chooseGoodsDraw(params);});
    $('#choose_goods_section_id').change(function(){chooseGoodsDraw(params);});
    
    if ( ! document.getElementById('choose_goods_list_box')) {
    containerEl.append('<div id="choose_goods_list_box" />');
    }
    $('#choose_goods_list_box').html('Загрузка...');

    loadGoods(params,function(containerElId,goods,total) {
        $('#choose_goods_list_box').html(goodsTable(goods,total));
    });
}

function chooseGoodsContainerEl(containerElId, triggerElement) {
    if (document.getElementById(containerElId)) {
        containerEl = $('#' + containerElId);
    } else {
        var containerEl = $('<div class="area hi" id="' + containerElId + '"></div>');
    }
    return containerEl;
}

function chooseGoodsParams(containerEl) {
    var tbl = $('<table id="choose_goods_params" class="simple tableforms width-100" />');
    var row = $('<tr class="labels" />');
            row.append('<td class="width-50">Название</div>')
            .append('<td class="width-50">Производитель</div>');
    tbl.append(row);
        row = $('<tr />');
            row.append('<td><input type="text" class="width-100" id="choose_goods_name" name="choose_good_param[name]" /></td>')
            .append('<td><select name="choose_good_param[brand_id]" id="choose_goods_brand_id" /></td>');
    tbl.append(row);
        row = $('<tr class="labels" />');
            row.append('<td>Артикул</div>')
            .append('<td>Раздел</div>');
    tbl.append(row);
        row = $('<tr />');
            row.append('<td><input type="text" class="width-100" id="choose_goods_code"  name="choose_good_param[code]" /></td>')
            .append('<td><select name="choose_good_param[section_id]" id="choose_goods_section_id" /><div id="ttte"></td>');
    tbl.append(row);
        
    containerEl.append(tbl);
    loadSections(function(data,params){
        for(i in data) {
            var optgroup = $('<optgroup label="' + data[i].name + '" />');
            if (data[i].children) {
                for(ci in data[i].children) {
                    optgroup.append('<option value="' + data[i].children[ci].id + '">' + data[i].children[ci].name + '</option>');
                }
            }
           $('<option value="0">Все разделы</option>').appendTo('#choose_goods_section_id');
           optgroup.appendTo('#choose_goods_section_id');
        }
    },null);
    loadBrands(function(data,params){
        $('#choose_goods_brand_id').append('<option value="0">Все бренды</option>');
        for(i in data) {
            $('#choose_goods_brand_id').append('<option value="' + data[i].id + '">' + data[i].name + '</option>');
        }
    },null);
}

function loadSections(onSuccess,params) {
    $.post('/od-men/json/sections',params).done(function(data) {
        if(data['profiling']) {
            $('#json_profiling').html(data['profiling']);
            delete data['profiling'];
        }
        onSuccess(data,params);
    });
}

function loadBrands(onSuccess,params) {
    $.post('/od-men/json/brands',params).done(function(data) {
        if(data['profiling']) {
            $('#json_profiling').html(data['profiling']);
            delete data['profiling'];
        }
        onSuccess(data,params);
    });
}

function loadGoods(params,onSuccess) {
    var containerElId = params['containerElId'];
    $.post('/od-men/json/goods',params).done(function(data) {
        if(data['profiling']) {
            $('#json_profiling').html(data['profiling']);
            data['profiling'] = null;
        }
        onSuccess(containerElId,data.goods,data.total);
    });
}

function loadPromoGoods(params,onSuccess) {
    $.post('/od-men/json/promo_goods',params).done(function(data) {
        if(data['profiling']) {
            $('#json_profiling').html(data['profiling']);
            delete data['profiling'];
        }
        if(data.messages) { delete data.messages;}
        onSuccess(data,params);
    });
}

/**
 * 
 * @param object $goods [{id:'123',name:'goodName',field:'value'},{...}]
 * @returns string <table><tr><td>id</td><td>name</td><td>field</td></tr><tr>...</tr></table>
 */
function goodsTable(goods,total) {
    var html = '<table class="listing"><tr><th><input type="checkbox" name="choose_goods_all" id="choose_goods_all" title="Отметить все" /></th><th>ID</th><th>Арт.</th><th>Группа</th><th>Наименование</th><th>Наличие</th><th>Активность</th></tr>';
    var i = 0;
    for (gn in goods) {
        html += '<tr ' + ((i++ % 2) === 0 ? 'class="odd"' : '') + '>';
        html += '<td><input type="checkbox" class="choose_goods_checkbox" name="choose_good[' + goods[gn].id + ']" id="choose_good_' + goods[gn].id + '" value="' + goods[gn].id + '" /></td>';
        html += '<td>';
        if (goods[gn].link_admin) {
            html += '<a href="' + goods[gn].link_admin + '" target="_blank">'
        }
        html += goods[gn].id;
        if (goods[gn].link_admin) {
            html += '</a>';
        }
        html += '</td>';
        html += '<td>' + goods[gn].code + '</td>';
        html += '<td>' + goods[gn].group_name + '</td>';
        html += '<td>' + goods[gn].name + '</td>';
        html += '<td>' + goods[gn].qty + '</td>';
        html += '<td>' + (goods[gn].active > 0 ? '<span class="green">акт</span>' : '<span class="red">неакт</span>') + '</td>';
        html += '</tr>';
    }
    html += '</table>';
    $obj = $ (html);
    $obj.find('#choose_goods_all').change(function(){
        if ( $ (this).is(':checked') ) {
            $ (this).closest('table').find('input.choose_goods_checkbox').prop('checked',true);
        } else {
            $ (this).closest('table').find('input.choose_goods_checkbox:checked').prop('checked',false);
            
        }
        
    });
    return $obj;
}

function bindedGoods(goods,model,id,alias,targetEl) {
        var html = '<table class="list">'
        for(i in goods) {
            if (goods[i]) {
                html += '<tr><td>';
                html += goods[i].id + '</td><td>' + goods[i].code + '</td><td>';
                html += goods[i].group_name + '</td><td>' + goods[i].name;
                html += '</td><td>' + goods[i].qty
                html += '</td><td>' + (goods[i].active > 0 ? '<span class="green">акт</span>' : '<span class="red">неакт</span>')
                html += '</td><td><a class="btn btn-round" href="/od-men/unbind/';
                html += model + '/' + id + '/' + alias + '/' + goods[i].id
                html += '">Удалить</a></td></tr>';
            }
        }
        html += '</table>'
        $(targetEl).html(html);
}

function bind(model,id,alias,far_keys,onSuccess) {
    $.post('/od-men/json/bind/' + model + '/' + id + '/' + alias ,{far_keys : far_keys}).done(function(data) {
        if(data['profiling']) {
            $('#json_profiling').html(data['profiling']);
            delete data['profiling'];
        }
        onSuccess(data['count']);
    });
}
