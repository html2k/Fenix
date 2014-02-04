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


    add(true);
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