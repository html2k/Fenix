;function HeaderSearch (){

    this.initStorage();
    this.init();
    this.bind();

    $('<link>')
        .appendTo($('head'))
        .attr({type : 'text/css', rel : 'stylesheet'})
        .attr('href', 'template/blocks/header-search/header-search.css?v='+ (new Date).getTime());

}



HeaderSearch.prototype.storage = {
    elem: {}
};
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
        .on('keyup', '.header-search__input', this.keyUp.bind(this));
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
        $.post('', {
            action: 'loadTemplate',
            controller: 'HeaderSearch',
            method: 'find',
            value: value
        }, function(result){

            that.storage.find[value] = result;
            that.viewFindeTemplate();

        });
    }else{
        that.viewFindeTemplate();
    }
}


/**
 * События нажатия клавиши
 * @param event
 */
HeaderSearch.prototype.keyUp = function(event){
    if (event.keyCode === 38){
        //Up

    } else if(event.keyCode === 40) {
        //Down

    } else if(event.keyCode === 13) {
        //Enter
        this.eventEnter();

    } else if(event.keyCode === 27) {
        //Esc
        this.eventEsc();

    }
}


/**
 * Запуск поиска по нажатию на enter
 */
HeaderSearch.prototype.eventEnter = function(){
    this.findValue(this.storage.elem.input.val());
}


/**
 * Нажал ESC закрыл окно
 */
HeaderSearch.prototype.eventEsc = function(){
    this.storage.elem.input.val('');
    this.hideView();
}


/**
 * Вставка результата выборки
 */
HeaderSearch.prototype.viewFindeTemplate = function(){
    var that = this,
        template = _.template(that.storage.template['header-search/result-board.html']),
        data = that.storage.find[that.storage.searchValue];

    this.storage.elem.viewBlock.html(template({ data: data }))
    that.showView();
};


/**
 * Показать окно
 */
HeaderSearch.prototype.showView = function(){
    this.storage.elem.input.addClass('header-search__input_focus');
    this.storage.elem.viewBlock.removeClass('dn');
};


/**
 * Скрыть окно
 */
HeaderSearch.prototype.hideView = function(){
    this.storage.elem.input.removeClass('header-search__input_focus');
    this.storage.elem.viewBlock.addClass('dn');
};


/**
 * Закрытие окна просмотра результатов при клике вне него
 * @param event
 */
HeaderSearch.prototype.domClick = function(event){
    var that = this,
        element = $(event.currentTarget);

    if(element.closest('.header-search').length || element.hasClass('.header-search')){
        return;
    }

    that.hideView();
}

new HeaderSearch;