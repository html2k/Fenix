$(function(){
    'use strict';

        $('.project-filter-objects span').on('mouseup', function(){
            var $this = $(this),
                $butten = $this.parent().children('span');

            if(!$this.hasClass('current')){
                var $list = $('.project-list li');

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

});