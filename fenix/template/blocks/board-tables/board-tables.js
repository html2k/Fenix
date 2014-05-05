Fx.required('b-dropdown');
Fx.required('b-popup');
Fx.required('b-select');

;function BoardTables (){
    this.initStorage();
    this.init();
    this.bind();
    $('.board-tables__item:first').trigger('click');


    $('<link>')
        .appendTo($('head'))
        .attr({type : 'text/css', rel : 'stylesheet'})
        .attr('href', 'template/blocks/board-tables/board-tables.css');
}

BoardTables.prototype.WIDGET_NAME = 'BoardTables';


/**
 * Инициализация хранилища
 */
BoardTables.prototype.initStorage = function(){
    if(!_.storage.get(this.WIDGET_NAME)){
        _.storage.set(this.WIDGET_NAME, {});
    }
};


/**
 * Загрузка данных
 */
BoardTables.prototype.init = function(){
    var that = this;
    $.post('', {
        action: 'loadTemplate',
        controller: that.WIDGET_NAME,
        method: 'getInfo',
        templates: [
            'board-tables/create-table-popup.html',
            'board-tables/template.html',
            'board-tables/col-item.html',
            'board-tables/main.html'
        ]
    }, function(result){
        var storage = _.storage.get(that.WIDGET_NAME);

        if(storage.current_table){
            that.current_table = storage.current_table;
        }

        that.storage = result;

        that.template();
    });
};


/**
 * Создание базового шаблона
 */
BoardTables.prototype.template = function(){
    var that = this,
        template = _.template(that.storage.template['board-tables/main.html']),
        tableTemplate = _.template(that.storage.template['board-tables/template.html']);

    $('.board-tables').html(template({
        tables: that.storage.tables,
        tableInfo: that.storage.tableInfo
    }));

    var box = $('.board-tables__box');

    that.storage.keys = [];
    _.each(that.storage.tables, function(table, key){
        that.storage.keys[key] = [];
        if(table.length){
            that.storage.keys[key] = _.keys(table[0]);
        }

        box.append(tableTemplate({
            type: '',
            keys: that.storage.keys[key],
            data: table
        }));
    });

    if(that.current_table){
        $('.board-tables__item[data-table='+that.current_table+']').trigger('click');
    }else{
        $('.board-tables__item:first').trigger('click');
    }
};


/**
 * Биндинг событий
 */
BoardTables.prototype.bind = function () {
    var that = this;
    $(document)
        .on('click', '.board-tables__remove-item', this.removeButton.bind(this))
        .on('change', '.board-tables__select-all', this.selectAllCell.bind(this))
        .on('click', '.board-tables__remove', this.removeCell.bind(this))
        .on('change', '.board-tables__select', this.selectCell.bind(this))
        .on('click', '.board-tables__item', this.itemClick.bind(this))
        .on('click', '.board-tables__create-table', this.showPopupCreateTable.bind(this))
        .on('click', '.board-tables__col-add', this.addCol.bind(this))
        //.on('click', '.board-tables__block td', function(){ that.editRow(this); })
        .on('input', '.board-tables__input', this.search.bind(this));
};


/**
 * Клик по списку, преключение видимых таблиц
 * @param event
 */
BoardTables.prototype.itemClick = function(event){
    var storage = _.storage.get(this.WIDGET_NAME),
        item = $(event.currentTarget),
        list = $('.board-tables__item'),
        blocks = $('.board-tables__block'),
        index = list.index(item);

    blocks.addClass('dn');
    blocks.eq(index).removeClass('dn');

    list.removeClass('board-tables__item_active');
    item.addClass('board-tables__item_active');

    $('.board-tables__input').val('');
    $('.board-tables__count-search').empty();

    storage.current_table = this.current_table = item.data('table');
    _.storage.set(this.WIDGET_NAME, storage);
};


/**
 * Ввод в поле поиска
 * @param event
 */
BoardTables.prototype.search = function(event){
    var that = this,
        box = $('.board-tables__box'),
        block = $('.board-tables__block');

    if(event.target.value.length < 1){
        $('.board-tables__count-search').empty();
        return;
    }

    $.post('', {
        action: 'loadTemplate',
        controller: that.WIDGET_NAME,
        method: 'search',
        value: event.target.value,
        table: this.current_table
    }, function(result){
        result.type = 'search';
        var template = _.template(that.storage.template['board-tables/template.html']),
            partial = $(template(result));

        $('.board-tables__block_temporarily').remove();
        partial.addClass('board-tables__block_temporarily');
        partial.removeClass('dn');
        block.addClass('dn');
        box.append(partial);
        $('.board-tables__count-search').html(result.data.length);

    });
};


/**
 * Редактирование ячейки
 * @param node
 */
BoardTables.prototype.editRow = function(node){
    var that = this;
    $('.board-tables__block_editable').each(function(){
        that.editableRow(node, false);
    });

    this.editableRow(node, !node.className.indexOf('board-tables__block_editable') > -1);
};


/**
 * Делает ячейку редактируемой или не редактируемой
 * @param node
 * @param is
 */
BoardTables.prototype.editableRow = function(node, is){
    var is = is ? is : false;

    node.setAttribute('contenteditable', is);

    if(is){
        $(node).addClass('board-tables__block_editable').focus();
    }else{
        $(node).removeClass('board-tables__block_editable');
    }
};


/**
 * Событие при клике на чекбокс в шапке
 * @param event
 */
BoardTables.prototype.selectAllCell = function(event){
    var is = $(event.currentTarget).is(':checked'),
        checkbox = $('.board-tables__block:visible .board-tables__select');

    if(is){
        checkbox
            .prop('checked', true)
            .addClass('board-tables__select_checked');
    }else{
        checkbox
            .prop('checked', false)
            .removeClass('board-tables__select_checked');
    }

    this.buttonRemoveIsActive();
};


/**
 * Событие при клике на чекбокс строки
 * @param event
 */
BoardTables.prototype.selectCell = function(event){
    var checkbox = $(event.currentTarget);

    if(checkbox.is(':checked')){
        checkbox
            .prop('checked', true)
            .addClass('board-tables__select_checked');
    }else{
        checkbox
            .prop('checked', false)
            .removeClass('board-tables__select_checked');
    }
    this.buttonRemoveIsActive();
};


/**
 * Активная/не активаная кнопка удаления
 */
BoardTables.prototype.buttonRemoveIsActive = function(){
    if($('.board-tables__block:visible .board-tables__select_checked').length){
        $('.board-tables__remove').removeClass('btn__disable');
    }else{
        $('.board-tables__remove').addClass('btn__disable');
    }
};


/**
 * Удаление строки
 * @param event
 */
BoardTables.prototype.removeCell = function(event){
    var that = this;
        button = $(event.currentTarget), cell = [];

    if(!button.hasClass('btn__disable')){

        var currentTable = that.current_table;

        $('.board-tables__select_checked').each(function(){
            var index = this.getAttribute('data-index');

            cell.push({
                table: currentTable,
                find: that.storage.tables[currentTable][index]
            });

        });

        $.post('', {
            action: 'loadTemplate',
            controller: that.WIDGET_NAME,
            method: 'removeCell',
            cell: cell
        }, function(){
            that.init();
        });
    }
};


/**
 * Событие при клике на крестик в таблице
 * @param event
 */
BoardTables.prototype.removeButton = function(event){
    $(event.currentTarget).parents('tr').find('th:first input').prop('checked', true).trigger('change');
    this.buttonRemoveIsActive();
    $('.board-tables__remove').trigger('click');

};


BoardTables.prototype.showPopupCreateTable = function(event){
    var that = this,
        template = _.template(that.storage.template['board-tables/create-table-popup.html']),
        col = _.template(that.storage.template['board-tables/col-item.html']);


    that.storage.popup = bPopup.show({
        name: 'Создание таблици',
        block: template({ item: col }),
        control: false
    });

    bSelect.initWidget();
};

BoardTables.prototype.addCol = function(){
    var that = this,
        template = _.template(that.storage.template['board-tables/col-item.html']);

    $('.board-tables__col-list').append(
        template({first: false})
    );
    bSelect.initWidget();
    bPopup.center(that.storage.popup);
};

new BoardTables;