Fx.required('b-select');
Fx.provide('fx.Rewrite');



fx.Rewrite = function(){


    /** Название блока */
    this.BLOCK_NAME = 'bRewrite';

    /** Контроллер */
    this.CONTROLLER_NAME = 'bRewrite';

    /** Хранилище */
    this.storage = {
        root: false // jQuery node
    };

    /** Базовый блок, весь контент вставляется туда */
    this.ROOT = '.seo-rewrite';

    /** Кнопки в меню */
    this.BUTTON_MENU = '.b-rewrite__name span';

    /** Класс не активной кнопки */
    this.BUTTON_MENU_CLASS = 'false-link';

    /** Переключаемые блоки */
    this.TAB_BLOCK = '.b-rewrite__block';

    /** Класс не активного таба */
    this.TAB_BLOCK_CLASS = 'dn';

    /** Блок редиректа */
    this.REWRITE_BOX_ITEM = '.b-rewrite__box';

    /** Элемент редиректа */
    this.REWRITE_ITEM = '.b-rewrite__edit-item';

    /** Кнопка удаления */
    this.REWRITE_BUTTON_REMOVE_ITEM = '.b-rewrite__remove-item';

    /** Форма */
    this.REWRITE_FORM = '.b-rewrite__form';


    /** Кнопка добавления правила */
    this.REWRITE_ADD_RULE = '.b-rewrite__add-rule';


    this.init();
    this.bind();

};


/**
 * Инициализация приложения
 */
fx.Rewrite.prototype.init = function(){
    var that = this;

    $.ajax({
        type: 'POST',
        data: {
            action: 'loadTemplate',
            controller: that.CONTROLLER_NAME,
            templates: [
                'b-rewrite/main.html',
                'b-rewrite/edit-item.html'
            ]
        },
        async: false,
        success: function(result){
            that.storage[that.BLOCK_NAME] = result;

            if(!(that.storage.root = $(that.ROOT)).length){

                $(function(){
                    if((that.storage.root = $(that.ROOT)).length){
                        that.pushMain();
                    }
                });

            }else{
                that.pushMain();
            }
        }
    });
};


/**
 * События
 */
fx.Rewrite.prototype.bind = function(){
    var that = this;
    $(document)
        .on('mouseup', that.REWRITE_ADD_RULE, that.addRule.bind(that))
        .on('submit', that.REWRITE_FORM, that.submit.bind(that))
        .on('click', that.REWRITE_BUTTON_REMOVE_ITEM, that.removeItem.bind(that))
        .on('click', that.BUTTON_MENU, that.tab.bind(that));
};


/**
 * Вставка контента
 */
fx.Rewrite.prototype.pushMain = function(){
    var that = this,
        template = fx.template(that.storage[that.BLOCK_NAME].template['b-rewrite/main.html']),
        params = {
            list: [],
            dict: [],
            editItem: fx.template(that.storage[that.BLOCK_NAME].template['b-rewrite/edit-item.html'])
        };

    params = fx.extend(params, that.storage[that.BLOCK_NAME]);

    that.storage.root.append(template(params));
};


/**
 * Добавить правило
 */
fx.Rewrite.prototype.addRule = function(){
    var that = this;

    that.addItem();
    bSelect.initWidget();
}


/**
 * Создание правила
 */
fx.Rewrite.prototype.addItem = function(){
    var that = this,
        box = $(that.REWRITE_BOX_ITEM),
        template = fx.template(that.storage[that.BLOCK_NAME].template['b-rewrite/edit-item.html']),
        param = fx.extend({
            item: {
                from: '',
                to: '',
                code: '301'
            }
        }, that.storage[that.BLOCK_NAME]);

    box.append(template(param));
};


/**
 * Удаление правила
 * @param event
 */
fx.Rewrite.prototype.removeItem = function(event){
    var that = this,
        element = $(event.target);

    if($(that.REWRITE_ITEM).length > 1){
        element.parents(that.REWRITE_ITEM).remove();
    }

};


/**
 * Переключение видимых областей
 * @param event
 */
fx.Rewrite.prototype.tab = function(event){
    var that = this,
        element = $(event.target),
        buttonList = $(that.BUTTON_MENU),
        tabs = $(that.TAB_BLOCK),
        index = buttonList.index(element);

    buttonList.addClass(that.BUTTON_MENU_CLASS);
    element.removeClass(that.BUTTON_MENU_CLASS);

    tabs.addClass(that.TAB_BLOCK_CLASS);
    tabs.eq(index).removeClass(that.TAB_BLOCK_CLASS);

    bSelect.initWidget();
};


/**
 * Отправка формы
 * @param event
 */
fx.Rewrite.prototype.submit = function(event){
    var that = this,
        items = $(that.REWRITE_ITEM),
        data = {
            action: 'loadTemplate',
            controller: that.CONTROLLER_NAME,
            controllerAction: 'saveList',
            option: []
        };

    items.each(function(){
        var self = $(this),
            from = self.find('input[name=from]'),
            to = self.find('input[name=to]'),
            code = self.find('input[name=code]')

        if(from.val() != ''){
            data.option.push({
                from: from.val(),
                to: to.val(),
                code: code.val()
            });
        }
    });

    $.post('', data, function(result){
        window.location = window.location.href;
    });

    event.preventDefault();
};


new fx.Rewrite;
