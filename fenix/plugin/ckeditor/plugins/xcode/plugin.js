CKEDITOR.plugins.add('xcode', {
    icons: 'xcode',
    init: function(editor) {

        var loadPopup = function(editor, value){
            var $popup = LIB.popup('xcode'),
                $area = $popup.find('textarea'),
                $save = $popup.find('.btn__success');

            if(value){
                $area.val(value);
            }

            $save.off('mouseup.xcode').on('mouseup.xcode', function(){
                var val = LIB.safe_tags_replace($area.val()),
                    inner = value ? value : '<pre><code>'+val+'</code></pre><p></p>';


                editor.insertHtml(inner);

                $area.val('');
                LIB.popup();
            });

        }


        editor.addCommand('xcodeCommand', {
            exec: function(editor){
                loadPopup(editor);
            }
        });

        editor.ui.addButton('xcode', {
            label   : 'xcode',
            command : 'xcodeCommand',
            toolbar : 'xcode'
        });



        editor.on( 'doubleclick', function( evt ){
            var element = CKEDITOR.plugins.link.getSelectedLink( editor ) || evt.data.element;


            if(element.is('code')){
                loadPopup(editor, element.$.innerHTML);
                element.$.innerHTML = '';
            }

        });


    }
});