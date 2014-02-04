GLOBAL.watch('structForm', function(obj){
    var button = $(obj.form).find('button.btn');
    if(obj.is){
        button.removeClass('btn__disable');
        button.removeAttr('disabled');
    }else{
        button.addClass('btn__disable');
        button.attr('disabled', 'disabled');
    }
});

GLOBAL.watch('beforeSend', function(){
    spin();
});