;function bSelect (){
    this.initStorage();
    this.bind();
    this.init(false);

    Fx.getStyle('template/blocks/b-select/b-select.css');
};


bSelect.prototype.WIDGET_NAME = 'bSelect';


/**
 * Локальное хранилище
 */
bSelect.prototype.initStorage = function(){
    if(!_.storage.get(this.WIDGET_NAME)){
        _.storage.set(this.WIDGET_NAME, {});
    }
};

/**
 * Инициализация, запрос шаблона
 * @param is
 */
bSelect.prototype.init = function (is) {
    var that = this;

    that.storage = _.storage.get(that.WIDGET_NAME);

    if(!is){
        that.storage = false;
    }

    if(that.storage){
        $(that.initWidget.bind(this));
        return;
    }

    $.ajax({
        type: 'POST',
        data: {
            action: 'loadTemplate',
            templates: ['b-select/b-select.html']
        },
        async: true,
        success: function(result){
            _.storage.set(that.WIDGET_NAME, result);
            that.init(true);
        }
    });
};


/**
 * Инициализация виджета
 */
bSelect.prototype.initWidget = function(){
    var that = this,
        template = _.template(that.storage.template['b-select/b-select.html']);


    $('span.b-select').each(function(){
        var self = $(this),
            select = self.find('.b-select__block');

        self.children('.b-select__name').width(select.innerWidth() - 15);
    });

    $('select.b-select').each(function(){
        var self = $(this),
            name = self.attr('name'),
            option = [],
            selected = 0;


        self.find('option').each(function(i){
            var value = this.value,
                text = this.innerHTML,
                selectedOption = this.hasAttribute('selected') && this.getAttribute('selected') === 'selected';

            if(selectedOption){
                selected = i;
            }
            option.push({
                value: value,
                text: text ? text : value
            });
        });

        var select = $(template({
            name: name,
            selectdOption: selected,
            option: option
        }));

        self.after(select);
        select.children('.b-select__name').width(select.children('.b-select__block').innerWidth() - 15);

        self.remove();
    });
};


/**
 * События
 */
bSelect.prototype.bind = function(){
    $(document)
        .on('mouseup', this.globalHide.bind(this))
        .on('mouseup', '.b-select__item', this.change.bind(this))
        .on('mouseup', '.b-select', this.show.bind(this));
};


/**
 * Открыть блок
 * @param event
 */
bSelect.prototype.show = function(event){
    var win = $(window),
        select = $(event.target);

    if(!select.hasClass('.b-select')){
        select = select.closest('.b-select');
    }
    var block = select.children('.b-select__block');

    this.hide();
    block.show();

    var blockPosition = block.offset(),
        blockHeight = block.innerHeight(),
        winTop = win.scrollTop(),
        winBottom = win.height() + winTop;


    if(winBottom < blockPosition.top + blockHeight){
        var top = blockPosition.top - blockHeight > 40 ? (blockPosition.top - blockHeight) : (blockPosition.top - 40);
        block.stop().animate({'marginTop': -top}, 200);
    }
};


/**
 * Закрыть блок/блоки
 */
bSelect.prototype.hide = function(){
    $('.b-select__block').hide().css('marginTop', '');
}


/**
 * Закрыть блоки при клике вне селекта
 * @param event
 */
bSelect.prototype.globalHide = function(event){
    var element = $(event.target);
    if(element.hasClass('.b-select') || element.closest('.b-select').length){
        return;
    }
    this.hide();
}


/**
 * Изменение значения
 * @param event
 */
bSelect.prototype.change = function(event){
    var item = $(event.target),
        select = item.parents('.b-select'),
        name = select.children('.b-select__name'),
        items = select.find('.b-select__item');

    name.html(item.html());
    this.hide();

    items.removeClass('b-select__item_active');
    item.addClass('b-select__item_active');

    select.find('.b-select__input').val(item.data('value'));

    event.stopPropagation();
}


window.bSelect = new bSelect();