$(function(){
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


//    $('.editor').ckeditor({
//        toolbar : [
//            [ 'Source' ],
//            [ 'RemoveFormat', 'ShowBlocks', 'Maximize' ],
//            [ 'Styles', 'Format', 'FontSize' ], [ 'TextColor', 'BGColor' ], [ 'About' ],
//            '/',
//
//            [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'],
//            [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
//            [ 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],
//            [ 'Table', 'Image', 'HorizontalRule', 'SpecialChar' ]
//        ]
//    });

    project();

});



function project () {
    'use strict';

    $('.js-projectDetailItemShow').hover(function(){
        $(this).parents('li').find('.js-projectDetailItem').show()
    }, function(){
        $(this).parents('li').find('.js-projectDetailItem').hide()
    });




}