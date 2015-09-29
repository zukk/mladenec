<script>
$(document).ready(function() {
    var comments_container = $('#comments-container');

    commentLink = function(el){
        var url_length = el.pathname.split('/').length, 
            url = el.href;
        
        if( url_length < 4 && el.href.indexOf('?id') == -1 ) {
            url += '/list';
        }         
        commentLoad(url);
        return false;
    }

    commentLoad = function(url){
        comments_container.empty().append('<i class="load"></i>');
        $('html, body').animate({ scrollTop: comments_container.offset().top - 20}, 'fast');

        $(comments_container).load(url, function(){
            updateLinks();
        });
    }
    
    commentLink(location);
});
</script>

<div id="comments-container"><i class="load"></i></div>