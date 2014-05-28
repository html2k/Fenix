$(function(){
	
	var $list = $('.project-list li');
    if(!$list.length) return;

    LIB.sort($('.js-sortable-list li'), function($list){
        spin('Подождите идет обработка');
        var action = {
            action: 'sortElem',
            id: []
        };
        $list.each(function(){
            action.id.push(this.getAttribute('data-id'));
        });
        $.post('', action, function(res){
            window.location = window.location.href;
        })
    });


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


    var key = {
        ctrl: false,
        shift: false
    }, lastItem = false, X, Y, $body = $('body'), allocation = false, items = [];
    $(document).on('keydown', function(event){
        var k = event.which;
        if(k === 91 || k === 93 || k === 17){
            key.ctrl = true;
        }

        if(k === 16){
            key.shift = true;
        }

    }).on('keyup', function(){
        var k = event.which;
        if(k === 91 || k === 93 || k === 17){
            key.ctrl = false;
        }

        if(k === 16){
            key.shift = false;
        }

    }).on('mouseup', '.project-list li', function(event){ // Выделение эелементов списка
        var $list = $('.project-list li'),
            $this = $(this);
        if(key.ctrl){
            $this.toggleClass('active');
        }else if(key.shift && lastItem){
            var lastIndex = $list.index(lastItem),
                selfIndex = $list.index($this),
                start = (lastIndex > selfIndex) ? selfIndex : lastIndex,
                end = (lastIndex > selfIndex) ? lastIndex : selfIndex;

            while(true){
                if(start > end) break;
                $list.eq(start).addClass('active');
                start++;
            }
        }else{
            $list.removeClass('active');
            $this.addClass('active');
        }

        if($this.hasClass('active')){
            lastItem = $this
        }
        GLOBAL.set('isSelectItem', $list);

    }).on('mouseup', '.js-projectEvent', function(){
        var data = {
            action: this.getAttribute('action'),
            parent: GLOBAL.get('page').id,
            id: []
        };
        if($('.project-list li.active').length){

            spin('Подождите');

            $('.project-list li.active').each(function(){
                data.id.push(this.getAttribute('data-id') *1);
            });

            $.post('', data, function(response){
                console.log(response)
                //window.location = window.location.href;
            });

        }

    }).on('click', '.spin', function(){
        spin(this.getAttribute('data-spiner'));
    });


    GLOBAL.watch('isSelectItem', function($list){
        if($list.is('.active')){
            $('.js-projectEvent').removeClass('disable');
        }else{
            $('.js-projectEvent').addClass('disable');
        }
    });

});