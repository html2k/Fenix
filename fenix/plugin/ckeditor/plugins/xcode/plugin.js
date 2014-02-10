CKEDITOR.plugins.add('xcode', {
    icons: 'xcode',
    init: function(editor) {

        var req = 'pre(!xcode)'

        editor.addCommand('xcodeCommand', {
            allowedContent: req,
            exec: function(editor){
                var pre = editor.document.createElement( 'pre' ),
                    code = editor.document.createElement( 'code' );

                pre.setAttribute('class', 'xcode');

                editor.insertElement(pre);
            }
        });

        editor.ui.addButton('xcode', {
            label   : 'xcode',
            command : 'xcodeCommand',
            toolbar : 'xcode'
        });


    }
});