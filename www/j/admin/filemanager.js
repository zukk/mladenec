$(document).ready(function() {
    (function( $ ) {

        var template = {
            paper : '<div id="filemanager-paper"><div id="filemanager-body" class="wait"><div class="units-row"><div class="unit-80"><h2>Файловый менеджер</h2></div><div class="unit-20 text-right"><span id="filemanager-close" class="btn btn-red">Закрыть</span></div></div><div id="filemanager-content"></div></div></div></div>'
        }

        var return_val_container = false;

        var methods =  {
            init : function( options ) { 
				
				var dir = $(this).attr('data-mdir') ? $(this).attr('data-mdir'): 0;
				
                return_val_container = $($(this).attr('data-filemanager'));

                $('body').append(template.paper);
                $('#filemanager-paper').hide();
                this.click(function(){
                    $('#filemanager-paper').show();

                    $.fn.filemanager('load',dir);
                });
                $('#filemanager-body').click(function(event){
                    event.stopPropagation();
                });
                $('#filemanager-paper, #filemanager-close').click(function(){
                    $('#filemanager-paper').hide();
                });
            },
            load : function(dirId, orderBy, orderDir) {
                $('#filemanager-content').html('');
                $('#filemanager-body').addClass('wait');

                if (typeof orderBy ===  'undefined') orderBy  = 'name';
                if (typeof orderDir === 'undefined') orderDir = 'asc';

                $.get(
                    "/od-men/ajax/filemanager.php",
                    {
                        mdir_id:   dirId,
                        order_by:  orderBy,
                        order_dir: orderDir
                    },
                    function(data) {
                        $('#filemanager-content').html(data);
                        $('#filemanager-body').removeClass('wait');
                        $('span[data-filemanager-load]').click(function(){
                            var dirId = $(this).attr('data-filemanager-load');
                            $.fn.filemanager('load', dirId, orderBy, orderDir);
                        });
                        $('div.filemanager-folders-add input[type=button]').click(function(){
                            var name = $('div.filemanager-folders-add input[type=text]').val();
                            var parent_id = $('#filemanager-current-dir-id').val();
                            //alert(value);
                            $.fn.filemanager('dirAdd',name,parent_id);
                        });
                        $('li[filemanager-file-path]').click(function(){
                           return_val_container.val($(this).attr('filemanager-file-path'));
                           $.fn.filemanager('close');
                        });
                    }
                );
            },
            dirAdd : function(name, parent_id) {
                $.get(
                    "/od-men/json/mdir_add",
                    {
                        name:      name,
                        parent_id: parent_id
                    },
                    function(data) {
                        $.fn.filemanager('load',data.id);
                    }
                );
            },
            close : function() {
                $('#filemanager-paper').hide();
            },
            update : function( content ) {
              // !!!
            }
        };

        $.fn.filemanager = function(method) {

            if ( methods[method] ) {
                return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
            } else if ( typeof method === 'object' || ! method ) {
                return methods.init.apply( this, arguments );
            } else {
                $.error( 'Метод с именем ' +  method + ' не существует для jQuery.filemanager' );
            }    



        };
    })(jQuery);

    $('[data-filemanager]').filemanager();
});