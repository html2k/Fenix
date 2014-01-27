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

});

    GLOBAL.watch('notification', function(message){
        if(message && message.option){
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


