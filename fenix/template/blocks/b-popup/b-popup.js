;function bPopup(){
    this.initStorage();
    this.init();
    this.bind();

    Fx.getStyle('template/blocks/b-popup/b-popup.css');
};

bPopup.prototype.WIDGET_NAME = 'bPopup';

/**
 * Локальное хранилище
 */
bPopup.prototype.initStorage = function(){
    if(!_.storage.get(this.WIDGET_NAME)){
        _.storage.set(this.WIDGET_NAME, {});
    }
};


/**
 * Инициализация, запрос шаблона
 * @param is
 */
bPopup.prototype.init = function (is) {
    var that = this;

    that.storage = _.storage.get(that.WIDGET_NAME);

    if(!is){
        that.storage = false;
    }

    if(that.storage){
        return;
    }

    $.ajax({
        type: 'POST',
        data: {
            action: 'loadTemplate',
            templates: ['b-popup/b-popup.html']
        },
        async: false,
        success: function(result){
            _.storage.set(that.WIDGET_NAME, result);
            that.init(true);
        }
    });
};


/**
 * События
 */
bPopup.prototype.bind = function () {
    $(document)
        .on('keyup', this.keyUp.bind(this))
        .on('click', '.b-popup__cancel', this.hide.bind(this))
        .on('click', '.b-popup', this.bHide.bind(this));
};


/**
 * Дефолтные опции
 * @param option
 * @returns {void|*}
 */
bPopup.prototype.optionExtend = function(option){
    option = _.extend({
        name: '',
        block: '',
        control: true
    }, option);


    if(_.isObject(option.block)){
        option.block = $('<span/>').append(option.block).html();
    }

    return option;
};


/**
 * Нажатие на клавишу
 * @param event
 */
bPopup.prototype.keyUp = function (event){
    if(event.keyCode === 27){
        this.hide(event);
    }
};


/**
 * Создать попап
 * @param option
 * @returns {*}
 */
bPopup.prototype.create = function(option){
    var that = this,
        option = this.optionExtend(option),
        template = _.template(that.storage.template['b-popup/b-popup.html']),
        popup = $(template(option));



    popup.hide = function(){
        this.remove();
    };
    popup.edit = function(option){
        popup.hide();
        that.show(option);
    };

    return popup;
};


/**
 * Показать попап
 * @param option
 */
bPopup.prototype.show = function (option) {
    var popup = this.create(option);
    $('body').css('overflow', 'hidden').append(popup);
    this.center(popup);

    popup.find('[autofocus]:first').focus();
    return popup;
};


/**
 * Закрытие по клику вне попапа
 * @param event
 */
bPopup.prototype.bHide = function(event){
    var element = $(event.target);

    if(element.hasClass('b-popup__box') || element.closest('.b-popup__box').length){
        return;
    }
    this.hide(event);
};


bPopup.prototype.center = function(popup){
    var fun = function(){
        var box = popup.children('.b-popup__box'),
            boxHeight = box.innerHeight(),
            popupHeight = popup.height();

        if(popupHeight > boxHeight){
            box.stop().animate({'marginTop': (popupHeight - boxHeight) / 2}, 'slow');
        }else{
            box.css('marginTop', '');
        }
    };

    setTimeout(fun, 1);
    $(window).resize(fun);
};


/**
 * Закрытие попапа
 * @param event
 */
bPopup.prototype.hide = function(event){
    if(!!event && !!event.target){
        var element = $(event.target).closest('.b-popup');
        if(!element.length){
            element = $('.b-popup:last');
        }
        element.remove();
    }else{
        $('.b-popup').remove();
    }

    $('body').css('overflow', '');
};

window.bPopup = new bPopup();