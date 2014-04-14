_.byteConvert = function(bytes){
    if      (bytes>=1000000000) {bytes=(bytes/1000000000).toFixed(2)+' GB';}
    else if (bytes>=1000000)    {bytes=(bytes/1000000).toFixed(2)+' MB';}
    else if (bytes>=1000)       {bytes=(bytes/1000).toFixed(2)+' KB';}
    else if (bytes>1)           {bytes=bytes+' bytes';}
    else if (bytes==1)          {bytes=bytes+' byte';}
    else                        {bytes='0 byte';}
    return bytes;
}

_.cutLine = function(string, size){
    var len = string.length;

    string = string.substr(0, size);
    if(len > size){
        string += '...';
    }

    return document.createTextNode(string).textContent;
}

_.storage = (function(){
    if('localStorage' in window && window['localStorage'] !== null){
        var s = localStorage;
        return {
            set : function(name, val){
                if(!name || !val) return false;

                var val = JSON.stringify(val);
                try {
                    s.setItem(name, val);
                    return true;
                } catch (e) {
                    return false;
                }
            },

            get : function(name){
                if(s[name])
                    return JSON.parse(s[name]);
                else
                    return false;
            },

            clear : function(){
                return s.clear();
            }
        };
    }else{
        return {
            set: function(){},
            get: function(){},
            clear: function(){}
        }
    }
}());

_.inArray = function(value, array){
    if(_.isArray(array)){
        return array.join('|').indexOf(value) > -1;
    }
    return false;
};


$(function(){
    'use strict';

    errorResize();

    if($.fn.ckeditor){
        $('.editor').ckeditor();

    }

    if($('.board-tables').length){
        Fx.required('board-tables');
    }
    if($('.header-search').length){
        Fx.required('header-search');
    }
    if($('.b-select').length){
        Fx.required('b-select');
    }
    if($('.b-dropdown').length){
        Fx.required('b-dropdown');
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


