$(document).ready(function () {
    $('#section td:last-child').addClass('last');
    $('#section td > div *').mouseenter(
        function (e) {
            $('#section td > div').removeClass('open');
            $(this).parents('div').addClass('open');
        }
    );
    $('#section td > div.open').live('mouseleave',
        function (e) {
            $(this).removeClass('open');
        }
    );
});