$(function(){ 
    'use strict';

    errorResize();

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

$(window).resize(errorResize);

function errorResize () {
    var $errorPage = $('.error-page');

    if($errorPage.length){
        var $parentError = $('body'),
            heightParent = $parentError.height(),
            heightPage = $errorPage.height();



        $errorPage.css({
            marginTop: (heightParent / 2) - (heightPage / 2)
        });
    }
}

function spin(text){
    $('.alpha').show();
    Spin.set($('.alpha-message'), text || 'Подождите идет загрузка');
}
function stopSpin(){
    Spin.remove($('.alpha-message'));
    $('.alpha').hide();
}


