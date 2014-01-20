$(function(){
    'use strict';


   $('.editor').ckeditor({
       toolbar : [
           [ 'Source' ],
           [ 'RemoveFormat', 'ShowBlocks', 'Maximize' ],
           [ 'Styles', 'Format', 'FontSize' ], [ 'TextColor', 'BGColor' ], [ 'About' ],
           '/',

           [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'],
           [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
           [ 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],
           [ 'Table', 'Image', 'HorizontalRule', 'SpecialChar' ]
       ]
   });

    project();

});

    GLOBAL.watch('notification', function(message){
        if(message.option){
            $.post('', {
                'action' : 'clearSystemMessage',
                'id': message.option.key
            });
        }
    });


function spin(text){
    $('.alpha').show();
    Spin.set($('.alpha-message'), text || 'Подождите идет загрузка');
}
function stopSpin(){
    Spin.remove($('.alpha-message'));
    $('.alpha').hide();
}

function project () {
    'use strict';

    var $list = $('.project-list li');
    $('.project-filter-objects span').each(function(){
        var code = this.getAttribute('data-code'),
            count = 0;

        if(code == 0){
            count = $list.length;
        }else{
            $list.each(function(){
                if(this.getAttribute('data-code') === code){
                    count++;
                }
            });
        }

        if(count){
            this.innerHTML += ' <sup>' +count+ '</sup>';
        }else{
            this.style.display = 'none';
        }
    }).on('mouseup', function(){
        var $this = $(this),
            $butten = $this.parent().children('span');

        if(!$this.hasClass('current')){

            $butten.removeClass('current');
            $this.addClass('current');

            $list.each(function(){

                if($this.data('code') === 0){
                    this.style.display = 'block';
                }else{
                    if(this.getAttribute('data-code') === $this.data('code')){
                        this.style.display = 'block';
                    }else{
                        this.style.display = 'none';
                    }
                }
            });
        }
    });


    /* Показать скрыть инфу про объект */
    $('.js-projectDetailItemShow').hover(function(){
        var position = $(this).offset(),
            $block = $(this).parents('li').find('.js-projectDetailItem');

        if(position.top - $block.innerHeight() <= 0){
            $block.css({
                'bottom' : 'auto',
                'top': '100%',
                'margin-top' : '10px'
            })
        }
        $block.show()
    }, function(){
        $(this).parents('li').find('.js-projectDetailItem').hide()
    });


    var X, Y, $body = $('body'), allocation = false, items = [];
    $(document).on('mousedown', function(event){
        if($(event.target).closest('.project-list li').length){
            X = undefined;
            Y = undefined;
            return;
        }
        X = event.pageX;
        Y = event.pageY;

        $(document).on('mousemove.allocation', function(event){
            var x = event.pageX, y = event.pageY;

            if(!allocation){
                items = [];


                $('.project-list li').each(function(){
                    var pos = $(this).offset();


                    pos.element = this;
                    pos.width = $(this).width();
                    pos.height = $(this).height();

                    items.push(pos);

                });


                allocation = $('<div class="allocation" />');
                $body.addClass('no-select').append(allocation);

            }

            var position = {left: X, top: Y};


            if(X > x){
                position.left = x;
                position.width = X - x;
            }else{
                position.width = x - X;
            }

            if(Y > y){
                position.top = y;
                position.height = Y - y;
            }else{
                position.height = y - Y;
            }

            allocation.css(position);

            var i = 0, len = items.length;
            for(; i < len; i++){
                console.log(


                )
            }



        });

    }).on('mouseup', function(){
        $(document).off('mousemove.allocation');
        if(allocation !== false){
            allocation.remove();
            allocation = false;
            $body.removeClass('no-select');
        }
    }).on('mouseup', '.project-list li', function(){
        $(this).toggleClass('active');

    }).on('mouseup', '.js-projectEvent', function(){
        var data = {
            action: this.getAttribute('action'),
            parent: GLOBAL.get('page').id,
            id: []
        };

        spin('Подождите');

        $('.project-list li.active').each(function(){
            data.id.push(this.getAttribute('data-id') *1);
        });

        $.post('', data, function(response){
            window.location = window.location.href;
        });
        

    }).on('click', '.spin', function(){
        console.log(1)
        spin(this.getAttribute('data-spiner'));
    });







}