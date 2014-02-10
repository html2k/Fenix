$(function(){ 
    'use strict';

    if($.fn.ckeditor){
        $('.editor').ckeditor();

    }
});

    GLOBAL.watch('notification', function(message){
        if(message && message.option){
            $.post('', {
                'action' : 'clearSystemMessage',
                'id': message.option.key
            }, function(res){
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


