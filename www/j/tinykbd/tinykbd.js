tinyKbd = {
    currentElement: null,
    isKey:          false,
    containerId:    'tinykbd_box',
    name:           'Виртуальная клавиатура',
    defaultLayout:  'Ru',
    currentLayout:  null,
    lastLayout:     null,
    isShift:        false,
    isLastShift:        false,
    dbgDraws:0,
    specialValue: {
        backspace:'<img src="/i/keyboard/backspace.png" />',
        leftarrow:'&larr;',
        shift:'<img src="/i/keyboard/shift.png" />',
        enter:'<img src="/i/keyboard/enter.png" />',
        rightarrow:'&rarr;',
        space:'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
    },
    layouts:{
        Ru:{
           default:[['ё','1','2','3','4','5','6','7','8','9','0','-','=','backspace'],
                    ['@','й','ц','у','к','е','н','г','ш','щ','з','х','ъ','\\'],
                    ['ф','ы','в','а','п','р','о','л','д','ж','э','enter'],
                    ['shift','я','ч','с','м','и','т','ь','б','ю','.','shift'],
                    ['layout','space','leftarrow','rightarrow']],
           shift:  [['Ё','!','"','№',';','%',':','?','*','(',')','_','+','backspace'],
                    ['@','Й','Ц','У','К','Е','Н','Г','Ш','Щ','З','Х','Ъ','/'],
                    ['Ф','Ы','В','А','П','Р','О','Л','Д','Ж','Э','enter'],
                    ['shift','Я','Ч','С','М','И','Т','Ь','Б','Ю',',','shift'],
                    ['layout','space','leftarrow','rightarrow']]
        },
        En:{
           default:[['`','1','2','3','4','5','6','7','8','9','0','-','=','backspace'],
                    ['@','q','w','e','r','t','y','u','i','o','p','[',']','\\'],
                    ['a','s','d','f','g','h','j','k','l',';','\'','enter'],
                    ['shift','z','x','c','v','b','n','m',',','.','/','shift'],
                    ['layout','space','leftarrow','rightarrow']],
           shift:  [['~','!','@','#','$','%','^','&','*','(',')','_','+','backspace'],
                    ['@','Q','W','E','R','T','Y','U','I','O','P','{','}','|'],
                    ['A','S','D','F','G','H','J','K','L',':','"','enter'],
                    ['shift','Z','X','C','V','B','N','M','<','>','?','shift'],
                    ['layout','space','leftarrow','rightarrow']]
        }
    },
    draw: function(layout) {
        this.dbgDraws++;
        $('#kbd_debug').html('Draw ' + this.dbgDraws);
        //alert(layout);
        $('#' + this.containerId).html('<div class="tinykbd_header"><span>[скрыть клавиатуру]</span></div>');
        var self = this;
        var html = '';
        var sign = '';

        if (this.layouts[layout]) {
            this.currentLayout = layout;
        } else {
            this.currentLayout = this.defaultLayout;
        }
        var shift_marker = 'default';
        if (this.isShift) shift_marker = 'shift';
        for (i in this.layouts[this.currentLayout][shift_marker]) {
            html = '<div class="tinykbd_layout_row">';
            for (x in this.layouts[this.currentLayout][shift_marker][i]) {
                var code = this.layouts[this.currentLayout][shift_marker][i][x];

                if(this.specialValue[code]) sign = this.specialValue[code];
                else                        sign = code;

                if('layout' == code) {
                    if('Ru' == this.currentLayout) sign = 'En';
                    else sign = 'Ru';
                }

                html += '<button type="button" value="' + code + '">' + sign + '</button>'
            }
            html += '</div>';
            $('#' + this.containerId).append(html);
        }
        $('#' + this.containerId + ' .tinykbd_header span').unbind('click');
        $('#' + this.containerId + ' button').unbind('click');
        $(document)
            .off('click', '#' + this.containerId + ' button')
            .off('click', '#' + this.containerId + ' .tinykbd_header span');
        $(document)
            .on('click', '#' + this.containerId + ' button', function() {
                self.keyHandler($(this).val());
            })
            .on('click', '#' + this.containerId + ' .tinykbd_header span', function() {
                self.currentElement = null;
                self.hide();
            });
        this.show();
    },
    keyHandler: function(keyValue) {
        sendValue = keyValue;
        switch (keyValue) {
            case 'backspace': sendValue = "{backspace}"; break;
            case 'enter':     sendValue = "{enter}";     break;
            case 'leftarrow': sendValue = "{leftarrow}"; break;
            case 'rightarrow':sendValue = "{rightarrow}";break;
            case 'space':     sendValue = " ";           break;
            case 'shift':
                this.isLastShift = this.isShift?false:true;
                this.isShift = this.isShift?false:true;
                sendValue = false;
                this.draw(this.currentLayout);
                break;
            case 'layout':
                sendValue = false;
                this.lastLayout = this.currentLayout;
                if('Ru' == this.currentLayout) this.draw('En');
                else this.draw('Ru');
                break;
        } 
        if (false !== sendValue) $(this.currentElement).sendkeys(sendValue);
        if (this.isShift && ('shift' !== keyValue)) {
            this.isShift = false;
            this.draw(this.currentLayout);
        }
        $(this.currentElement).focus();
        return true;
    },

    init: function(selector) {
        var self = this;
        $('body').append('<div id="tinykbd_gap"></div><div id="' + this.containerId + '"></div>');
        $(document)
            .off('click', 'body')
            .off('click', '#' + this.containerId)
            .off('click', selector);
        $(document)
            .on('click', 'body', function() {
                self.currentElement = null;
                self.hide();
            })
            .on('click', '#' + this.containerId, function(event) { event.stopPropagation(); })
            .on('click', selector, function(event) {
                self.currentElement = event.target;
                if (null === self.currentLayout) {
                    self.draw(self.defaultLayout);
                } else {
                    self.draw(self.currentLayout);
                }
                self.show(event.target);
                event.stopPropagation();
            });
            tinyKbd.hide();
    },
    show: function() { 
        $('#' + this.containerId).show();
        $('#tinykbd_gap').show(); 
        $('#tinykbd_gap').css('height',$('#' + this.containerId).height())
    },
    hide: function() { $('#' + this.containerId).hide();$('#tinykbd_gap').hide(); this.currentElement = null;},
    keyPressed: function(element) {
        var value = $(element).children('span.active').val();
        this.keyHandler(value);
    }
}