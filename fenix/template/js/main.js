$(function(){ 
    'use strict';


   $('.editor').ckeditor({
       extraPlugins: 'xcode',

       toolbar : [
           [ 'Source' ],
           [ 'RemoveFormat', 'ShowBlocks', 'Maximize' ],
           [ 'Styles', 'Format', 'FontSize' ], [ 'TextColor', 'BGColor' ],

           [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'],
           [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
           [ 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink' ],
           [ 'Table', 'Image', 'HorizontalRule', 'SpecialChar', 'xcode' ]
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


