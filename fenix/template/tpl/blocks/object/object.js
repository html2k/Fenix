$(function(){
    var $list = $('.object-list');
    if(!$list.length) return;

    var add = function(is){
        $.post('', {action : 'getItemObject', index : $list.children().length}, function(respons){
            $list.append(respons);
            $('.ctrl-select').ctrlSelect();

            $list = $('.object-list');
            var $item = $list.children(),
                count = $item.length;

            $item.each(function(key, val){
                this.style.zIndex = count - key;
            });

            if(is){
                loadParam($item.eq(0).find('.js-setting'), '', count);
            }

        });
        },
        loadParam = function($block, value, key){
            $.post('', {action : 'getGist', type : value, key : key}, function(respons){
                $block.empty().append(respons);
                $('.ctrl-select', $block).ctrlSelect();
            });
        }


    GLOBAL.watch('objectType', function(obj){
        var parent = obj.block.parents('.js-object-item');
        loadParam(parent.find('.js-setting'), obj.value, $list.index(parent));
    });


    if($list.children().length < 1){
        add(true);
    }
    $(document).on('click', '.js-showSetting', function(){
        var $this = $(this),
            $li = $this.parents('li'),
            $setting = $li.find('.js-setting');
        if($this.hasClass('active')){
            $setting.slideUp(300);
            $this.removeClass('active');
        }else{
            $setting.slideDown(300);
            $this.addClass('active');
        }
    });
    $('.js-add').click(add);
});

$(document).on('keydown', '.js-param-list div.inp', function(event){
    if(event.keyCode === 13){
        event.preventDefault();

        var divValue = this.innerHTML,
            $parent = $(this).parents('.js-param-list'),
            $inp = $parent.find('input.inp'),
            $list = $parent.find('ul'),
            inputValue = $inp.val(),
            obj = [];


        if(inputValue !== ''){
            if(json = JSON.parse(inputValue)){
                obj = json;
            }
        }



        obj.push(divValue);
        $inp.val(JSON.stringify(obj));
        $list.append('<li>'+divValue+'</li>');

        this.innerHTML = '';
    }
}).on('mouseup', '.js-param-list li', function(event){
    var $this = $(this),
        $parent = $this.parents('.js-param-list'),
        $list = $parent.find('ul li'),
        $inp = $parent.find('input.inp'),
        index = $list.index($this),
        value = $inp.val(),
        json;

    if(json = JSON.parse(value)){
        if(json[index]){
            json.splice(index,1);
        }
    }


    $this.remove();
    $inp.val(JSON.stringify(json));
});



$(document).on('mouseup', '.js-object-icon-list .object-icon-list i', function(){
    var $this = $(this),
        $listIcon = $this.parent().children(),
        $I = $('.js-object-icon i'),
        $input = $('.js-object-icon-input'),
        cls = this.getAttribute('data-class');

    $I.attr('class', cls);
    $input.val(cls);

    $listIcon.removeClass('active');
    $this.addClass('active');

    LIB.popup();
});

$(document).on('mouseup', '.js-removeItem', function(){
    var $li = $(this).parents('li'),
        $list = $li.parent().children();
    if($list.length > 1){
        $li.remove();
    }
});