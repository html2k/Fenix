;function Fx (){
}

Fx.prototype._requireds = {};
Fx.prototype.required = function(blockName){
    if(this._requireds[blockName]){
        return;
    }
    $.getScript('template/blocks/' +blockName+ '/' + blockName + '.js');
    this._requireds[blockName] = 1;
};

Fx.prototype.getStyle = function(link){
    $('<link>')
        .appendTo($('head'))
        .attr({type : 'text/css', rel : 'stylesheet'})
        .attr('href', link +'?v='+ (new Date).getTime());
};

window.Fx = new Fx;



/* ================================================================== Global variables class === */
var GLOBAL = function(){
    var issetCallstack = function(name, lib){
        if(lib.changeCallstack[name]){
            var i = 0; len = lib.changeCallstack[name].length;
            for(; i < len; i++){
                if(is(lib.changeCallstack[name][i], 'Function')){
                    lib.changeCallstack[name][i](lib.var[name]);
                }
            }
        }
    }

    var lib = {
        var : {},
        changeCallstack : {},

        get : function(name){
            return this.var[name];
        },
        set : function(name, value){
            this.var[name] = value;
            issetCallstack(name, this);
            return this;
        },
        unset : function(name){
            delete this.var[name];
            issetCallstack(name, this);
            return this;
        },
        watch : function(name, callstack){
            if(this.changeCallstack[name]){
                this.changeCallstack[name].push(callstack);
            }else{
                this.changeCallstack[name] = [callstack];
            }
            //issetCallstack(name, this);
            return this.changeCallstack[name].length -1;
        },
        unwatch : function(name, id){
            if(this.changeCallstack[name]){
                this.changeCallstack[name][id] = false;
            }
            return true;
        }
    };

    return lib;
}();

var LIB = function(){

    var tagsToReplace = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;'
    };

    return {
        escapeHtml: function (str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        },


        unescapeHtml: function (escapedStr) {
            var div = document.createElement('div');
            div.innerHTML = escapedStr;
            var child = div.childNodes[0];
            return child ? child.nodeValue : '';
        }
    };
}();

/* ================================================================== Global Function === */
function is (el, type){
    var clas = Object.prototype.toString.call(el).slice(8, -1);
    return el !== undefined && el !== null && clas === type;
}
function inArray(array, needle){
    return !!array.filter(function(item){ return item == needle }).length
}
function doGetCaretPosition (ctrl) {
    'use strict';
	var CaretPos = 0, Sel;	// IE Support
	if (document.selection) {
		Sel = document.selection.createRange();
        ctrl.focus();
		Sel.moveStart('character', -ctrl.value.length);
		CaretPos = Sel.text.length;
	} else if (ctrl.selectionStart || ctrl.selectionStart === '0') {
        // Firefox support
		CaretPos = ctrl.selectionStart;
    }
	return (CaretPos);
};
function setCaretPosition (ctrl, pos) {
	'use strict';
    if (ctrl.setSelectionRange) {
		ctrl.focus();
		ctrl.setSelectionRange(pos, pos);
	} else if (ctrl.createTextRange) {
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
};
function getChar (event) {
    'use strict';
    if (event.which === null) {
        if (event.keyCode < 32) { return null; }
        return String.fromCharCode(event.keyCode);
    }

    if (event.which !== 0 && event.charCode !== 0) {
        if (event.which < 32) { return null; }
        return String.fromCharCode(event.which);
    }
    
    return null;
};

function dump(list){
    var result = [],
        dampRec = function(list, delim){
            var i, pr = delim, probel = '&#160;&#160;&#160;';

            if(is(list, 'Object')){
                result.push('Object { ');
                pr = delim + probel;
            }else if(is(list, 'Array')){
                result.push('Array { ');
                pr = delim + probel;
            }

            for(i in list){
                if(is(list[i], 'Object' || is(list[i], 'Array'))){
                    result.push('\n' + pr);
                    result.push(pr + i + ' : ')
                    dampRec(list[i], pr + probel);
                }else{
                    result.push('\n' + pr + i + ': ' + list[i])
                }
            }
            if(is(list, 'Object') || is(list, 'Array')){
                result.push('\n' + delim + ' }');
            }
        }

    dampRec(list, '');
    return result.join('');
}


/* ================================================================== Validate === */
(function () {
    'use strict';
    var element = '[isNumber], [isEn], [isRu], [isEmpty]';

    LIB.validate = function(element){
        var value = element.value,
            isNumber = element.hasAttribute('isNumber'),
            isEn = element.hasAttribute('isEn'),
            isRu = element.hasAttribute('isRu'),
            isEmpty = element.hasAttribute('isEmpty'),
            is = true;


        if (isNumber && !/^[0-9]$/.test(value)) {
            is = false;
        }

        if (isEn && /[а-яёА-ЯЁ]/.test(value)) {
            is = false;
        }

        if (isRu && /[a-zA-Z]/.test(value)) {
            is = false;
        }

        if (isEmpty && value.length < 1){
            is = false;
        }

        return is
    };

    $(document).on('input', element, function () {
        if (LIB.validate(this)) {
            $(this).removeClass('invalid');
        } else {
            $(this).addClass('invalid');
        }
        
    }).on('blur', element, function () {
        var value = this.value,
            isEmpty = this.hasAttribute('isEmpty');
        if(isEmpty && value === '') {
            $(this).addClass('invalid');
        }else if (value === '') {
            $(this).removeClass('invalid');
        }
    });
}());

/* ================================================================== Modifier === */
(function () {
    'use strict';
    
    
    var element = '[tsanslateCHPU], [onlyNumbers], [onlyRu], [onlyEn]',
        lib = {
            onlyNumbers : function (e) {
                e = e || event;
                if (e.ctrlKey || e.altKey || e.metaKey) { return; }
                var chr = getChar(e);
                if (chr === null) { return; }
                if (chr < '0' || chr > '9') { return false; }
            },
            allOnlySelf : function (el, action) {
                var position = doGetCaretPosition(el),
                    regEx = /[.]/g,
                    len = 0;
                
                if (action === 'en') {
                    regEx = /[а-яёъА-ЯЁЪ]/g;
                } else if (action === 'ru') {
                    regEx = /[a-zA-Z]/g;
                }

                len = el.value.match(regEx);
                len = len !== null ? len.length : 0;
                
                el.value = el.value.replace(regEx, '');
                setCaretPosition(el, position - len);
                
            },
            translateCHPU : function (el) {
                var A = [], value, pos, CHAR, i;
                A["Ё"] = "YO";
                A["Й"] = "I";
                A["Ц"] = "TS";
                A["У"] = "U";
                A["К"] = "K";
                A["Е"] = "E";
                A["Н"] = "N";
                A["Г"] = "G";
                A["Ш"] = "SH";
                A["Щ"] = "SCH";
                A["З"] = "Z";
                A["Х"] = "H";
                A["Ъ"] = "";
                A["ё"] = "yo";
                A["й"] = "i";
                A["ц"] = "ts";
                A["у"] = "u";
                A["к"] = "k";
                A["е"] = "e";
                A["н"] = "n";
                A["г"] = "g";
                A["ш"] = "sh";
                A["щ"] = "sch";
                A["з"] = "z";
                A["х"] = "h";
                A["ъ"] = "";
                A["Ф"] = "F";
                A["Ы"] = "I";
                A["В"] = "V";
                A["А"] = "A";
                A["П"] = "P";
                A["Р"] = "R";
                A["О"] = "O";
                A["Л"] = "L";
                A["Д"] = "D";
                A["Ж"] = "ZH";
                A["Э"] = "E";
                A["ф"] = "f";
                A["ы"] = "i";
                A["в"] = "v";
                A["а"] = "a";
                A["п"] = "p";
                A["р"] = "r";
                A["о"] = "o";
                A["л"] = "l";
                A["д"] = "d";
                A["ж"] = "zh";
                A["э"] = "e";
                A["Я"] = "YA";
                A["Ч"] = "CH";
                A["С"] = "S";
                A["М"] = "M";
                A["И"] = "I";
                A["Т"] = "T";
                A["Ь"] = "";
                A["Б"] = "B";
                A["Ю"] = "YU";
                A["я"] = "ya";
                A["ч"] = "ch";
                A["с"] = "s";
                A["м"] = "m";
                A["и"] = "i";
                A["т"] = "t";
                A["ь"] = "";
                A["б"] = "b";
                A["ю"] = "yu";
                A[" "] = "_";
                A["/"] = "_";
                A["&"] = "and";
                
                
                value = el.value;
                pos = doGetCaretPosition(el);
                value = value.split('');
                for (i = 0; i < value.length; i += 1) {
                    CHAR = value[i];
                    if (A[CHAR] !== undefined) {
                        value[i] = A[CHAR];
                        
                        if (A[CHAR].length > 1) {
                            pos += A[CHAR].length - 1;
                        }
                    }
                }
                el.value = value.join('').toLowerCase();
                setCaretPosition(el, pos);
    
            }
            
        };
    
    
    
    $(document).on('input', element, function () {
        var value = this.value,
            translateCHPU = this.hasAttribute('tsanslateCHPU'),
            onlyRu = this.hasAttribute('onlyRu'),
            onlyEn = this.hasAttribute('onlyEn');
        
        if (translateCHPU) {
            lib.translateCHPU(this);
        }
        
        if (onlyRu) {
            lib.allOnlySelf(this, 'ru');
        }
        
        if (onlyEn) {
            lib.allOnlySelf(this, 'en');
        }
    }).on('keypress', element, function (event) {
        var onlyNumbers = this.hasAttribute('onlyNumbers');
        
        if (onlyNumbers) {
            return lib.onlyNumbers(event);
        }
    });
    
}());

/* ================================================================== Mask === */
(function () {
    'use strict';
    $(document).on('input', '[mask]', function () {

        var i, format = '##.##.####',
            delim = '#',
            reg = /[A-Za-zА-ЯЁа-яё0-9]/,
            el = this,
            value = el.value,
            pos = doGetCaretPosition(el);
    
        if (!value.length) {
            return;
        }

        if (this.getAttribute('mask') !== '') {
            format = this.getAttribute('mask');
        }
        
        format = format.split('');
        value = value.split('');

        if (value.length > format.length) {
            value = value.splice(0, format.length);
        }

        for (i = 0; i < value.length; i += 1) {
            if (!reg.test(value[i])) {
                value.splice(i, 1);
            }
        }

        for (i = 0; i < value.length; i += 1) {
            if (format[i] !== delim) {
                value.splice(i, 0, format[i]);

                if (i + 1 === pos) { pos += 1; }

            }
        }
    
        el.value =   value.join('');
        setCaretPosition(el, pos);
        

    });

}());


/* ================================================================== Help === */
(function () {
    'use strict';
    $(document).on('mouseover', '[help]', function () {
        var text, node, isInit = this.helpInit,
            pos = $(this).offset();
        
        text = this.getAttribute('help');
        node = document.createElement('SPAN');
        node.className = 'help-block';
        document.body.appendChild(node);
        node.innerHTML = text;
        this.helpInit = node;
       

        this.helpInit.style.top = (pos.top - (this.helpInit.offsetHeight + 7)) + 'px';
        this.helpInit.style.left = pos.left + 'px';
        
    }).on('mouseout', '[help]', function () {
        var isInit = this.helpInit;
        if (isInit) {
            this.helpInit.parentNode.removeChild(this.helpInit);
        }
    });
}());


/* ================================================================== Dropdown === */
(function () {
    'use strict';

    var $window = $(window),
        widnowHeight = $window.height(),
        windowWidth = $window.width();

    $window.resize(function(){
        widnowHeight = $window.height();
        windowWidth = $window.width();
    });

    var hideDropdown = function () {
            $('.dropdown > .dropdown-block').css({'display': 'none', 'z-index': 1});
        },
        showDropdown = function (el) {
            hideDropdown();
            var documentHeight = $(document).height(),
                windowSctollTop = $window.scrollTop(),
                $dropdown = $(el.parentNode),
                $block = $dropdown.children('.dropdown-block');

            $block[0].style.display = 'block';
            $block[0].style.zIndex = 9;


            var dropdownPosition = $dropdown.offset(),
                blockHeight = $block.innerHeight(),
                blockWidth = $block.innerWidth(),
                topPosition = 'dropdown__bottom',
                leftPosition = 'dropdown__right';


            var isTop = !(dropdownPosition.top + blockHeight > documentHeight) || !(dropdownPosition.top + blockHeight > $window.height());

            var isLeft = false;
            if(dropdownPosition.left + blockWidth > windowWidth){
                isLeft = true;

                if(dropdownPosition.left - blockWidth < 0){
                    isLeft = false;
                }
            }

            if(isTop || (dropdownPosition.top - blockHeight - 10 < 0)){
                $dropdown.addClass(topPosition);
            }else{
                $dropdown.removeClass(topPosition);
            }
            if(isLeft){
                $dropdown.addClass(leftPosition);
            }else{
                $dropdown.removeClass(leftPosition);
            }
            $dropdown.find('[autofocus]').focus();

        };
    $(document).on('mouseup', '.dropdown-name', function () {
        showDropdown(this);
    }).on('mouseup', function (event) {
        var el = event.target;
        if ($(el).hasClass('dropdown') || $(el).parents('.dropdown').length) {
            return;
        }
        hideDropdown();
    }).on('keyup', function (event) {
        if (event.keyCode === 27) {
            hideDropdown();
        }
    }).on('dropdown-close', function(){
        hideDropdown();
    });
}());


/* ================================================================== Popup === */
(function () {
    'use strict';
    var callStack = [],
        resize = function () {
            $('.popup:visible').each(function () {
                var $e = $(this),
                    wHeight = $(window).height(),
                    pHeight = $e.innerHeight();
                
                if (pHeight < wHeight - 40) {
                    $e.css('margin-top', (wHeight / 2) - (pHeight / 2));
                } else {
                    $e.css({'margin-top': ''});
                }
            });
        },
        eventCallStack = function (action, $popup, role) {
            var i, len = callStack.length;
            for (i = 0; i < len; i += 1) {
                if (callStack[i].role && callStack[i].role === role) {
                    if (callStack[i][action]) {
                        callStack[i][action]($popup);
                        resize();
                    }
                }
            }
        },
        close = function () {
            $('.popup-alpha').remove();
            $('.popup:visible').each(function () {
                if ($(this).parent('.box-popup')) {
                    $(this).hide().unwrap();
                    eventCallStack('close', $(this), this.getAttribute('role'));
                    $('body').css('overflow', 'auto');
                }
            });
        };


    LIB.popup = function(role){
        if(role){
            var $popupList = $('.popup'),
                $body = $('body'),
                $popup = $popupList.closest('[role=' + role + ']:first'),
                $close,
                $alpha,
                $wrap,
                wHeight,
                pHeight;

            if ($popup.length) {
                $alpha = $('<div class="popup-alpha"/>');
                $wrap = $('<div class="box-popup"/>');
                $popup.wrap($wrap);
                $body.append($alpha);
                $body.css('overflow', 'hidden');

                eventCallStack('open', $popup, role);

                wHeight = $(window).height();
                pHeight = $popup.height();
                if (pHeight < wHeight - 40) {
                    $popup.css('margin-top', (wHeight / 2) - (pHeight / 2));
                }

                $popup.show();
                $popup.find('[popup-close]').on('mouseup.popup-close', close);
                $popup.find('[autofocus]').focus();
                return $popup;
            }
        }else{
            close();
        }
    };

    $(window).resize(function () {
        resize();
    });
    $(document).on('mouseup.popup', '[popup]', function (event) {
        LIB.popup(this.getAttribute('popup'));
        return false;
    }).on('mouseup.popup-close', function (event) {
        var el = $(event.target);
        if (event.target.tagName) {
            if (el.hasClass('popup') || el.parents('.popup').length || event.target.hasAttribute('popup')) {
                return;
            }
        }
        close();
    }).on('keyup.popup', function (event) {
		if (event.keyCode === 27) {
			close();
		}
	});
    
    $.fn.popupResize = resize;
    $.fn.popup = function (param) {
        callStack.push(param);
    };
}());


/* ================================================================== Spinner === */
var Spin = (function () {
    'use strict';
    var lib = {};
    lib.set = function ($element, text) {
        var $spin = $('<div class="spinner"></div>'),
            template = ['<span class="spinner-block">'];
        
        if (text !== undefined) {
            template.push('<span class="spinner-text">' + text + '</span>');
        }
        template.push('<span class="spinner-line"></span></span>');
        $spin.append(template.join(''));

        $element.children().hide();
        $element.append($spin);
    };
    
    lib.edit = function ($element, text) {
        $element.find('.spinner-text').text(text);
    };
    
    lib.remove = function ($element, option) {
        $element.find('.spinner').fadeTo(300, 0, function () {
            $(this).remove();
        });
        $element.children().fadeTo(300, 1);
    };
    
    return lib;
}());


/* ================================================================== Notification === */
var Notification = (function () {
    'use strict';
    var lib = {},
        mes = [],
        mesIndex = 0,
        opts = {
            message : '',
            flag : '',
            show : false,
            hide : true
        },
        show = function (list, index) {
            var c = 0, len = list.length - 1,
                init = function (el) {
                    var clear = setTimeout(function () {
                        Notification.remove(el.data('index'));
                    }, 3000);
                    el.off('mouseover.notif').on('mouseover.notif', function () {
                        clearTimeout(clear);
                    }).off('mouseout.notif').on('mouseout.notif', function () {
                        init(el);
                    });
                };
            list.each(function (k, e) {
                $(this).fadeTo(50 + (k + 1) * 100, 1, function () {
                    if (list.eq(len - k).data('hide') === true) {
                        init(list.eq(len - k));
                    }
                });

            });
        };
    lib.set = function (option) {
        var template = $('<div class="notification-item"/>');
        option = $.extend({}, opts, option);

        template.addClass('notification-item__' + option.flag);
        template.html(option.message);

        mes.push({
            element : template,
            index : mesIndex += 1,
            hide : option.hide,
            option: option
        });

        if (option.show) {
            Notification.show();
        }
        return template;
    };


    lib.show = function () {
        var i, len = mes.length, template,
            event = function () {
                Notification.remove(this.getAttribute('data-index'));
            };

        if ($('.notification').length < 1) {
            $('body').append('<div class="notification"/>');
        }
        template = $('.notification');

        for (i = 0; i < len; i += 1) {
            mes[i].element.on('click', event);
            mes[i].element.attr('data-index', mes[i].index);
            mes[i].element.attr('data-hide', mes[i].hide);
            template.prepend(mes[i].element);
        }
        $('body').append(template);
        show(template.children());
    };

    lib.remove = function (index) {
        var i = 0, len = mes.length;
        if (!isNaN(index)) {
            index = parseInt(index, 10);
            var message = false;
            for (i = 0; i < mes.length; i += 1) {
                if (mes[i].index === index) {
                    message = mes.splice(i, 1);
                }
            }
            GLOBAL.set('notification', message[0]);
            $('.notification [data-index=' + index + ']').fadeTo(300, 0, function () {
                $(this).remove();
                if ($('.notification').children().length < 1) {
                    $('.notification').remove();

                }
            });
        }
    };
    return lib;
}());


/* ================================================================== Tab === */
(function(){
    $(document).on('mouseup', '.tab-btn', function(){
        var $this = $(this),
            $parent = $this.parents('.tab-box'),
            $btn = $parent.find('.tab-btn'),
            $blocks = $parent.find('.tab-block'),
            index = $btn.index($this);

        if(!$this.hasClass('active')){
            $blocks.slideUp(200, function(){
            }).eq(index).slideDown(200, function(){
                $blocks.removeClass('active').eq(index).addClass('active');
                $btn.removeClass('active').eq(index).addClass('active');
            });
        }
    });
}());


/* ================================================================== Form === */
(function(){

    $(function(){

        $('form').each(function(){

            if(this.hasAttribute('watch') && this.getAttribute('watch') !== ''){
                var $this = $(this),
                    invalid = false;
                $this.find('input, select, textarea').each(function(){
                    if(!LIB.validate(this)) invalid = true;
                });

                GLOBAL.set(this.getAttribute('watch'), {is: !invalid, form: this});
            }
        });
    });

    $(document).on('input', 'form', function(){
        var $this = $(this),
            invalid = false;


        if(this.hasAttribute('watch') && this.getAttribute('watch') !== ''){
            $this.find('input, select, textarea').each(function(){
                if(!LIB.validate(this)) invalid = true;
            });

            GLOBAL.set(this.getAttribute('watch'), {is: !invalid, form: this});
        }

    }).on('submit', 'form', function(event){
        var $this = $(this),
            invalid = false,
            data = {},
            elements = [];

        $this.find('input, select, textarea').each(function(){
            $(this).trigger('input').trigger('blur');
            if($(this).hasClass('invalid')) invalid = true;
            elements.push(this);
            data[this.name] = this.value;
        });

        if (invalid) {
            var error = this.hasAttribute('error') && this.getAttribute('error') !== '' ? this.getAttribute('error') : false;
            if(error !== false){
                GLOBAL.set(error, {
                    data: data,
                    elements: elements,
                    form: $this
                });
            }
            event.preventDefault();
        } else {
            var action = this.hasAttribute('action') && this.getAttribute('action') !== '' ? this.getAttribute('action') : '',
                method = (this.hasAttribute('method') && this.getAttribute('method') !== '') ? this.getAttribute('method') : 'POST',
                ajaxSuccess = this.hasAttribute('ajax-success') && this.getAttribute('ajax-success') !== '' ? this.getAttribute('ajax-success') : false,
                submit = this.hasAttribute('submit') && this.getAttribute('submit') !== '' ? this.getAttribute('submit') : 'POST',
                beforeSend = this.hasAttribute('before-send') && this.getAttribute('before-send') !== '' ? this.getAttribute('before-send') : false,
                send = this.hasAttribute('send') && this.getAttribute('send') !== '' ? this.getAttribute('send') : false;

            if(beforeSend !== false){
                GLOBAL.set(beforeSend, {
                    data: data,
                    elements: elements,
                    form: $this
                });
            }
            if (this.hasAttribute('ajax')) {
                $.ajax({
                    url: action,
                    type: method,
                    data: data,
                    success: function (response) {
                        if (ajaxSuccess) GLOBAL.set(ajaxSuccess, {
                            data: data,
                            response: response,
                            elements: elements,
                            form: $this
                        });
                    }
                });
                event.preventDefault();
            }

            if (send) GLOBAL.set(send, 1);
        }
    })
}());


/* ================================================================== Select === */
(function(){

    $.fn.ctrlSelect = function(){
        this.each(function(){
            var $this = $(this),
                $option = $this.children('option'),
                $list = $('<ul class="btn-select"/>'),
                checkName = false;

            $option.each(function(){
                var li = $('<li/>');
                li.text(this.innerHTML);
                $list.append(li);
                if($(this).is(':checked')){
                    checkName = this.innerHTML;
                }
            });

            if(checkName === false){
                checkName = $option.eq(0).text()
            }

            if(!$this.parent('.js-btn-select').length){
                $this.wrap('<span class="btn js-btn-select"/>');
            }

            var parent = $this.parent();


            var $cloneUl = $list.clone();
            $('body').append($cloneUl);
            $cloneUl.css('float', 'left');

            $list.width($cloneUl.width());
            $cloneUl.remove();

            if(!parent.find('.btn-in').length){
                parent.append($('<span class="btn-in btn-in__first">'+ checkName +'</span>'));
                parent.append($('<i class="btn-in btn-tail btn-in__last" />'));
                parent.append($list);
            }else{
                parent.find('.btn-select').remove();
                parent.find('.btn-select');
                parent.append($list);
            }

            $this.hide();
        });
    };

    var hideSelect = function(){
        $('.js-btn-select').removeClass('btn__active').children('ul').hide();
    };

    $(document).on('mouseup', '.js-btn-select', function(){
        hideSelect();
        var $this = $(this),
            $select = $this.find('select'),
            $option = $select.find('option'),
            $ul = $this.children('ul'),
            $li = $ul.children('li');

        $ul.show();

        $li.off('click').on('click', function(){
            var index = $li.index($(this)),
                value = $option.eq(index).val();

            $select.val(value);
            $option.attr('selected', false).eq(index).attr('selected', true);



            if($select.attr('watch') && $select.attr('watch') !== ''){
                GLOBAL.set($select.attr('watch'), {
                    value: value,
                    select: $select,
                    block: $this
                });
            }

            $this.children('.btn-in.btn-in__first').text($(this).text());
            hideSelect();
        });

        $this.addClass('btn__active');
    }).on('mouseup', function(event){
        if(!$(event.target).closest('.js-btn-select').length){
            hideSelect();
        }
    });

    $(function(){
        $('.ctrl-select').ctrlSelect();
    });
}());


/* ================================================================== Sort === */
(function(){
    LIB.sort = function($list, callback){

        $list.off('mousedown.sort').on('mousedown.sort', function(event){
            var t = this,
                baseX = event.pageX,
                baseY = event.pageY,
                $this = $(t),
                $body = $('body'),
                list = [], $copy, $rezerv;

            $body.addClass('no-select');
            $body.css('cursor', 'move');

            $(document).off('mousemove.sort').on('mousemove.sort', function(event){
                var x = event.pageX,
                    y = event.pageY;

                if(baseX === x && baseY !== y) return;

                if(!$copy){
                    $rezerv = $this.clone();
                    $copy = $this.clone();
                    $this.after($copy);

                    $copy.addClass('js-pointer').css({
                        background: 'rgba(250,125,125,0.9)'
                    });

                    $this.remove();

                    $list = $list.parent().children(t.tagName.toLowerCase());

                    $list.each(function(){
                        var $t = $(this),
                            pos = $t.offset();
                        list.push({
                            top: pos.top,
                            left: pos.left,
                            width: $t.width(),
                            height: $t.height()
                        });
                    });
                }

                var i = 0, len = list.length;
                for(; i < len; i++){
                    if(y > list[i].top && y < list[i].top + list[i].height){
                        var del = list[i].top + (list[i].height / 2);

                        if(y < del){
                            $list.eq(i).before($copy);
                        }else{
                            $list.eq(i).after($copy);
                        }
                    }
                }


            }).off('mouseup.sort').on('mouseup.sort', function(){
                $(document).off('mousemove.sort').off('mouseup.sort');
                $body.removeClass('no-select');
                $body.css('cursor', 'auto');


                if($copy && $copy.length && $rezerv && $rezerv.length){
                    $copy.after($rezerv);
                    $copy.remove();

                    $list = $list.parent().children(t.tagName.toLowerCase());

                    if(is(callback, 'Function')){
                        callback($list);
                    }

                    LIB.sort($list, callback);
                }
            });

        });

    };

}());


/* ================================================================== Others === */
(function(){
    $(document).on('mouseup', '.ctrl-box', function(event){
        event.stopPropagation();
        $(this).find('input:visible').eq(0).focus();
    });
}());
