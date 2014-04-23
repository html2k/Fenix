CKEDITOR.plugins.add( 'filemanager', {
    icons: 'filemanager',
    init : function( editor ){
        var pluginName = 'FileManager',
            title = '',
            className = 'fileManager';


        bFileManager.change(function(path, fileName, fileSize){

            var fileType = /[^.]+$/.exec(path);

            if(_.inArray(fileType, ['png', 'jpg', 'jpeg', 'bmp'])){
                var node = new CKEDITOR.dom.element( 'img' );

                node.setAttribute('src', path);

            }else{
                var node = new CKEDITOR.dom.element( 'a' );

                node.setAttribute('href', path);
                node.setText(fileName + ' (' + fileSize + ')');
            }

            editor.insertElement(node);
        });

        editor.ui.addButton( 'filemanager',
            {
                label : title,
                command : pluginName,
                click:function(){
                    bFileManager.show();
                }
            }
        )
    }
});