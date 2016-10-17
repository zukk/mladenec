/**
 * @name		jQuery Countdown Plugin
 * @author		Martin Angelov
 * @version 	1.0
 * @url			http://tutorialzine.com/2011/12/countdown-jquery/
 * @license		MIT License
 */


$(function(){

    /*var note = $('#note'),*/
    ts = new Date(2016, 04, 30, 00, 00, 00),
    newYear = true;

    if((new Date()) > ts){
        // The new year is here! Count towards something else.
        // Notice the *1000 at the end - time must be in milliseconds
        ts = (new Date()).getTime() + 10*24*60*60*1000;
        newYear = false;
    }

    $('#countdown').countdown({
        timestamp	: ts,
        callback	: function(days, hours, minutes, seconds){

            var message = "";

            message += days + " day" + ( days==1 ? '':'s' ) + ", ";
            message += hours + " hour" + ( hours==1 ? '':'s' ) + ", ";
            message += minutes + " minute" + ( minutes==1 ? '':'s' ) + " and ";
            message += seconds + " second" + ( seconds==1 ? '':'s' ) + " <br />";

            if(newYear){
                message += "left until the new year!";
            }
            else {
                message += "left to 10 days from now!";
            }

            /*note.html(message);*/
        }
    });

});

(function($){

    // Number of seconds in every time division
    var days	= 24*60*60,
        hours	= 60*60,
        minutes	= 60;


    // Creating the plugin
    $.fn.countdown = function(prop){

        var options = $.extend({
            callback	: function(){},
            timestamp	: 0
        },prop);

        var left, d, h, m, s, positions;

        // Initialize the plugin
init(this, options);

        positions = this.find('.position');

        (function tick(){

            // Time left
            left = Math.floor((options.timestamp - (new Date())) / 1000);

            if(left < 0){
                left = 0;
            }

            // Number of days left
            d = Math.floor(left / days);
            updateDuo(1, 2, d, d >= 100);
            left -= d*days;


            // Number of hours left
            h = Math.floor(left / hours);
            updateDuo(3, 4, h);
            left -= h*hours;

            // Number of minutes left
            m = Math.floor(left / minutes);
            updateDuo(5, 6, m);
            left -= m*minutes;

            // Number of seconds left
            s = left;
            updateDuo(7, 8, s);

            // Calling an optional user supplied callback
            options.callback(d, h, m, s);

            // Scheduling another call of this function in 1s
            setTimeout(tick, 1000);
        })();

        // This function updates two digit positions at once
        function updateDuo(minor,major,value,hundred){
            switchDigit(positions.eq(minor),Math.floor(value/10)%10);
            switchDigit(positions.eq(major),value%10);
            var h = positions.eq(0);
            if (hundred === true) {
                switchDigit(h,Math.floor(value/100)%10);
                h.show();
            } else if (hundred === false) {
                h.hide();
            }
        }

        return this;
    };


    function init(elem, options){
        elem.addClass('countdownHolder');

        // Creating the markup inside the container
        $.each(['Days','Hours','Minutes','Seconds'],function(i){
            var position = '<span class="position">\
          <span class="digit static">0</span>\
        </span>';

            var positions = [];
            var i = (this == 'Days') ? 3 : 2
            while (i--) {
                positions.push(position)
            }
            positions = positions.join('')
            $('<span class="count'+this+'">').html(positions).appendTo(elem);

            if(this!="Seconds"){
                elem.append('<span class="countDiv countDiv'+i+'"></span>');
            }
        });

    }

    // Creates an animated transition between the two numbers
    function switchDigit(position,number){

        var digit = position.find('.digit')

        if(digit.is(':animated')){
            return false;
}

        if(position.data('digit') == number){
            // We are already showing this number
            return false;
        }

        position.data('digit', number);

        var replacement = $('<span>',{
            'class':'digit',
            css:{
                top:'-2.1em',
                opacity:0
            },
            html:number
        });

        // The .static class is added when the animation
        // completes. This makes it run smoother.

        digit
            .before(replacement)
            .removeClass('static')
            .animate({top:'2.5em',opacity:0},'fast',function(){
                digit.remove();
            })

        replacement
            .delay(100)
            .animate({top:0,opacity:1},'fast',function(){
                replacement.addClass('static');
            });
    }
})(jQuery);


