;function bDropdown (){
    this.init();
    this.bind();

    Fx.getStyle('template/blocks/b-dropdown/b-dropdown.css');
};


bDropdown.prototype.WIDGET_NAME = 'bDropdown';


/**
 * Локальное хранилище
 */
bDropdown.prototype.initStorage = function(){
    if(!_.storage.get(this.WIDGET_NAME)){
        _.storage.set(this.WIDGET_NAME, {});
    }
};


/**
 * Загрузка шаблона
 */
bDropdown.prototype.loadTemplate = function(){
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
            templates: ['b-dropdown/b-dropdown.html']
        },
        async: false,
        success: function(result){
            _.storage.set(that.WIDGET_NAME, result);
            that.storage = _.storage.get(that.WIDGET_NAME);
            that.init(true);
        }
    });
}



bDropdown.prototype.init = function(){
    $(window).resize();
};


/**
 * Собятия
 */
bDropdown.prototype.bind = function(){
    $(document)
        .on('click', this.globalHide.bind(this))
        .on('click', '.b-dropdown__name', this.show.bind(this));
};


/**
 * Открыть дропдаун
 * @param event
 */
bDropdown.prototype.show = function(event){
    var self = $(event.target);

    self.parents('.b-dropdown:first')
        .children('.b-dropdown__block').show()
        .find('.inp:first').focus();
};


/**
 * Закрыть все дропдауны
 * @param event
 */
bDropdown.prototype.globalHide = function(event){
    var that = this,
        self = $(event.target);

    if(self.hasClass('b-dropdown') || self.parents('.b-dropdown').length > 0){
        return;
    }
    $('.b-dropdown').each(function(){
        that.hide(this);
    });
};


/**
 * Закртыть дропдаун
 * @param element
 */
bDropdown.prototype.hide = function(element){
    if(element){
        $(element).children('.b-dropdown__block').hide();
    }else{
        $('.b-dropdown__block').hide();
    }
};


bDropdown.prototype.create = function(option){
    this.loadTemplate();
    var template = _.template(this.storage.template['b-dropdown/b-dropdown.html']),
        option = _.extend({
        name: 'undefined',
        block: ''
    }, option);

    option.block = $('<div/>').append(option.block).html();

    return template(option);
};

window.bDropdown = new bDropdown();