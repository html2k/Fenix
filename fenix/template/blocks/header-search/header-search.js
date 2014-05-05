Fx.required('b-popup');


;function HeaderSearch (){

    this.initStorage();
    this.init();
    this.bind();

    Fx.getStyle('template/blocks/header-search/header-search.css');
}


HeaderSearch.prototype.storage = {
    elem: {},
    blockVisible: false
};
HeaderSearch.prototype.ACTIVE_ITEM_CLASS = 'header-search-result-block__item_active';
HeaderSearch.prototype.is_cursor = false;

HeaderSearch.prototype.WIDGET_NAME = 'HeaderSearch';


/**
 * Инициализация хранилища
 */
HeaderSearch.prototype.initStorage = function(){
    if(!_.storage.get(this.WIDGET_NAME)){
        _.storage.set(this.WIDGET_NAME, {});
    }
};


/**
 * Инициализация виджета
 */
HeaderSearch.prototype.init = function(){
    var that = this;
    $.post('', {
        action: 'loadTemplate',
        templates: ['header-search/main.html', 'header-search/result-board.html']
    }, function(result){
        that.storage = _.extend(that.storage, result);
        that.createMainTemplate();
    });
};


/**
 * Загрузка базового шаблона
 */
HeaderSearch.prototype.createMainTemplate = function(){
    var that = this,
        template = _.template(that.storage.template['header-search/main.html']);

    $('.header-search').html(template({}));

    that.storage.elem.viewBlock = $('.header-search-result-block');
    that.storage.elem.input = $('.header-search__input');
};


/**
 * События
 */
HeaderSearch.prototype.bind = function(){
    $(document)
        .on('mouseup', this.domClick.bind(this))
        .on('input', '.header-search__input', this.input.bind(this))
        .on('keydown', '.header-search__input', this.keyDown.bind(this))
        .on('keydown', this.documentKeyDown.bind(this));
};


/**
 * Событие ввода в поиске
 * @param event
 */
HeaderSearch.prototype.input = function(event){
    var that = this,
        value = ($.trim(event.currentTarget.value)).toLowerCase();

    that.hideView();


    if(!that.storage.find){
        that.storage.find = {};
    }

    if(value.length > 3){
        this.findValue(value);
    }
};


/**
 * Отправка значения на сервер
 * @param value
 */
HeaderSearch.prototype.findValue = function(value){
    var that = this;

    that.storage.searchValue = value;
    if(!that.storage.find[value]){

        $.ajax({
            type: 'POST',
            data: {
                action: 'loadTemplate',
                controller: 'HeaderSearch',
                method: 'find',
                value: value
            },
            async: false,
            success: function(result){
                that.storage.find[value] = result;
                that.viewFindeTemplate();
            }
        });

    }else{
        that.viewFindeTemplate();
    }
};


/**
 * События нажатия клавиши
 * @param event
 */
HeaderSearch.prototype.keyDown = function(event){
    if (event.keyCode === 38){
        //Up
        this.eventUp(event);

    } else if(event.keyCode === 40) {
        //Down
        this.eventDown(event);

    } else if(event.keyCode === 13) {
        //Enter
        this.eventEnter(event);

    } else if(event.keyCode === 27) {
        //Esc
        this.eventEsc(event);
    }
};


/**
 * События при нажатии клавиши
 * @param event
 */
HeaderSearch.prototype.documentKeyDown = function(event){
    var key1 = this.storage.keydown,
        key2 = event.keyCode;

    if(key1 === 18 || key1 === 83){
        if(key2 === 18 || key2 === 83 & key2 !== key1){
            $('.header-search__input').focus();
            bPopup.hide();
            $(window).scrollTop(0);
            event.preventDefault();
        }
    }

    this.storage.keydown = event.keyCode;
};


/**
 * Запуск поиска по нажатию на enter
 */
HeaderSearch.prototype.eventEnter = function(){

    if(this.is_cursor){
        var a = this.is_cursor.find('a');
        window.location = a[0].href;
    }else{
        var value = this.storage.elem.input.val();
        if(value.length){
            this.findValue(value);
        }
    }
};


/**
 * Нажал ESC закрыл окно
 */
HeaderSearch.prototype.eventEsc = function(){
    this.storage.elem.input.val('');
    this.hideView();
};


/**
 * Листаем вниз
 */
HeaderSearch.prototype.eventDown = function(event, isUp){
    event.preventDefault();
    if(!this.storage.blockVisible){
        this.eventEnter();
    }

    var list = this.storage.elem.list,
        active = list.filter('.' + this.ACTIVE_ITEM_CLASS);

    if(!active.length){
        list.eq(isUp ? list.length -1 : 0).addClass(this.ACTIVE_ITEM_CLASS);
        this.is_cursor = list.eq(isUp ? list.length -1 : 0);
        return;
    }

    var index = list.index(active),
        to = isUp ?
                (index - 1 >= 0 ? index - 1 : list.length -1) :
                (index + 1 < list.length ? index + 1 : 0);

    list.removeClass(this.ACTIVE_ITEM_CLASS);
    list.eq(to).addClass(this.ACTIVE_ITEM_CLASS);
    this.is_cursor = list.eq(to);

    this.scrollTo(list.eq(to));
};


/**
 * Листаем вверх
 */
HeaderSearch.prototype.eventUp = function(event){
    this.eventDown(event, true);
};


/**
 * Скролл блока вверх/низ
 * @param element
 */
HeaderSearch.prototype.scrollTo = function(element){
    var ul = this.storage.elem.viewBlock.children('ul'),
        li = this.storage.elem.viewBlock.find('.header-search-result-block__item'),
        index = li.index(element),
        top = 0;


    li.slice(0, index).each(function(){
        top += $(this).height();
    });

    ul.stop().animate({ 'scrollTop': top }, 'slow');
};


/**
 * Вставка результата выборки
 */
HeaderSearch.prototype.viewFindeTemplate = function(){
    var that = this,
        template = _.template(that.storage.template['header-search/result-board.html']),
        data = that.storage.find[that.storage.searchValue];

    this.storage.elem.viewBlock.html(template({ data: data }))
    this.storage.elem.list = this.storage.elem.viewBlock.find('.header-search-result-block__item');
    that.showView();
};


/**
 * Показать окно
 */
HeaderSearch.prototype.showView = function(){
    this.is_cursor = false;
    this.storage.blockVisible = true;
    this.storage.elem.input.addClass('header-search__input_focus');
    this.storage.elem.viewBlock.removeClass('dn');
};


/**
 * Скрыть окно
 */
HeaderSearch.prototype.hideView = function(){
    this.is_cursor = false;
    this.storage.blockVisible = false;
    this.storage.elem.input.removeClass('header-search__input_focus');
    this.storage.elem.viewBlock.addClass('dn');
};


/**
 * Закрытие окна просмотра результатов при клике вне него
 * @param event
 */
HeaderSearch.prototype.domClick = function(event){
    var that = this,
        element = $(event.target);

    if(element.closest('.header-search').length || element.hasClass('.header-search')){
        return;
    }

    that.hideView();
};

new HeaderSearch;