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
            issetCallstack(name, this);
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


/* ================================================================== Validate === */
(function () {
    'use strict';
    var element = '[isNumber], [isEn], [isRu]';
    $(document).on('input', element, function () {
        var value = this.value,
            isNumber = this.hasAttribute('isNumber'),
            isEn = this.hasAttribute('isEn'),
            isRu = this.hasAttribute('isRu'),
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
        
        if (is) {
            $(this).removeClass('invalid');
        } else {
            $(this).addClass('invalid');
        }
        
    }).on('blur', element, function () {
        var value = this.value;
        if (value === '') {
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


            var isTop = false;
            if(dropdownPosition.top - blockHeight < windowSctollTop){
                isTop = true;

                if(dropdownPosition.top + blockHeight > documentHeight){
                    isTop = false;
                }

            }

            var isLeft = false;
            if(dropdownPosition.left + blockWidth > windowWidth){
                isLeft = true;

                if(dropdownPosition.left - blockWidth < 0){
                    isLeft = false;
                }
            }


            if(isTop){
                $dropdown.addClass(topPosition);
            }else{
                $dropdown.removeClass(topPosition);
            }
            if(isLeft){
                $dropdown.addClass(leftPosition);
            }else{
                $dropdown.removeClass(leftPosition);
            }


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
            $('.popup').each(function () {
                if ($(this).parent('.box-popup')) {
                    $(this).hide().unwrap();
                    eventCallStack('close', $(this), this.getAttribute('role'));
                }
            });
            $('body').css('overflow', 'auto');
        };
    $(window).resize(function () {
        resize();
    });
    $(document).on('mouseup.popup', '[popup]', function (event) {
        var $popupList = $('.popup'),
            role = this.getAttribute('popup'),
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
            event.stopPropagation();
        }
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
		template.text(option.message);
		
		mes.push({
			element : template,
			index : mesIndex += 1,
			hide : option.hide
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
			for (i = 0; i < mes.length; i += 1) {
				if (mes[i].index === index) {
					mes.splice(i, 1);
				}
			}
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


/* ================================================================== Tags === */
(function (){
    'use strict';

    var tags = ['x-code'],
        findTag = function (node){
            var i, len = tags.length;
            for(i = 0; i < len; i++){
                if(node.tagName.toLocaleLowerCase() === tags[i]){
                    xCollection(node, tags[i]);
                }
            }
        };

    setTimeout(function(){
        /* Ищем наши теги */
        var allTags = document.getElementsByTagName('*'),
            i, len = allTags.length;
        for(i = 0; i < len; i++){
            findTag(allTags[i]);
        }

    }, 0);


    function xCollection(node, tagName){
        var lib = {
            'x-code' : function(node){ /* ==================== X-CODE */
                var pre = document.createElement('PRE'),
                    code = document.createElement('CODE'),
                    toogle = node.getAttribute('toogle'),
                    data = document.createTextNode(node.getAttribute('data'));
                pre.className = 'x-code';
                pre.appendChild(code);
                code.appendChild(data);

                if(toogle && toogle != 'false'){
                    var span = document.createElement('SPAN');
                    span.innerHTML = 'Расскрыть';
                    span.className = 'x-code_btn';
                    pre.style.display = 'none';
                    span.onclick = function(){
                        if(pre.style.display === 'none'){
                            pre.style.display = 'block';
                            span.innerHTML = 'Закрыть';
                        }else{
                            pre.style.display = 'none';
                            span.innerHTML = 'Расскрыть';
                        }
                    };
                    pre.style.display = 'none';
                    node.parentNode.insertBefore(span, 0);
                }

                node.parentNode.insertBefore(pre, 0);
                node.parentNode.removeChild(node);
            },
            'x-text' : function(node){ /* ==================== X-TEXT */

            }
        };

        if(lib[tagName])
            lib[tagName](node);
    }

}());