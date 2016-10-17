<form id="section-form" action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <p>
        <label for="name">Викимарт категории</label>
    </p>

    {$wiki_categories = ORM::factory('wikicategories')->find_all()->as_array()}

    {assign var='res' value = array()}
    {foreach from=$wiki_categories key=k item=categories}
        {$res[$categories->parent_id][$categories->category_id] = $categories}
    {/foreach}

    {function build_tree res=0 parent_id=0 only_parent=false}
        {if $parent_id ==0}
            <div id="jstree" style="display:none">
        {/if}
        {if (is_array($res) and isset($res.$parent_id))}
            <ul>
                {if ($only_parent == false)}
                    {foreach $res.$parent_id as $cat}
                        <li class="level_{$cat->category_id}" id="{$cat->category_id}">
                            {$cat->name}
                            {build_tree res=$res parent_id=$cat->category_id}
                        </li>
                    {/foreach}
                {/if}
            </ul>
        {/if}
        {if $parent_id ==0}
            </div>
        {/if}
    {/function}


    <p class="forms-inline">

        <div class="area wiki_area" id="goods_a">
            {include file='admin/good/chosen.tpl'}
        </div>
    </p>
    <div class="do wiki_do">
        <input name="edit" value="Сохранить" type="submit" class="btn btn-green"/>
        {*<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn btn-green" alt="list" />*}
    </div>
    <div class="wiki_do wiki_del">
        <input name="del" value="Убрать из категории" type="button" class="btn btn-red btn_del"  onclick="$('#goods_a' +
         ' ' + '.trdel').click()"/>
    </div>

    {build_tree res=$res parent_id=0}
    <input type="hidden" name="wiki_cat_id" id="wiki_cat_id" value="">


    <script>
        $(function(){
            $(document).on('click', 'input[type=button].trdel', function() {
                var val_del = $(this).closest('input[type=button]').prev().val();
                $.ajax({
                    url: {Route::url('admin_valupd')},
                    method: 'POST',
                    data: {
                        id: val_del
                    },
                    dataType: 'json'
                });
            });

            // create an instance when the DOM is ready
            $.jstree.defaults.core.themes.variant = "small";
            $('#jstree').on('loaded.jstree', function(e, data) {
                $("#goods_a").hide();
                var wiki_cat_id = $("#wiki_cat_id").val();
                if(wiki_cat_id) {
                    $('#jstree').jstree(true).select_node('#' + wiki_cat_id);
                }
            });
            $('#jstree').jstree({
                "plugins" : [ "wholerow" ]
            });

            // bind to events triggered on the tree
            $('#jstree').on("changed.jstree", function (e, data) {
                $("#goods_a").show();
                $(".wiki_do").show();
                var id_ckecked = data.selected;

                $.ajax({
                    url: '/admin/getwikigoods.php',
                    method: 'POST',
                    data: {
                        id: id_ckecked
                    },
                    dataType: 'HTML',
                    success: function(data){
                        $('#goods_a').empty();
                        $('#goods_a').append(data);
                    }
                });

                if(id_ckecked.length == 0 || id_ckecked.length == 1) {
                    $("#wiki_cat_id").val(id_ckecked);
                } else {
                    var id_ckecked_z = data.selected[0];
                    $("#wiki_cat_id").val(id_ckecked_z);
                }
            });
            $('#jstree').show();
        });
    </script>

</form>

