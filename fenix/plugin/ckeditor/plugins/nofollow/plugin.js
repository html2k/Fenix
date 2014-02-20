CKEDITOR.plugins.add('nofollow', {
    init: function(editor) {

        var testNode = document.createElement('A'),
            host = window.location.host;

        editor.dataProcessor.htmlFilter.addRules({
            elements :{
                a : function( element ){
                    if( element.attributes.href && element.attributes.href.length > 1 ){
                        testNode.href = element.attributes.href;
                        if(testNode.host !== host){
                            if ( !element.attributes.rel ){
                                element.attributes.rel = 'nofollow';
                            }
                        }

                    }
                }
            }
        });

    }
});