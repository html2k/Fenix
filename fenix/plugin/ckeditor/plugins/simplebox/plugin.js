CKEDITOR.plugins.add( 'simplebox', {
    requires: 'widget',

    icons: '',

    init: function( editor ) {

        editor.widgets.add( 'simplebox', {
            // Widget code.
        } );

        editor.widgets.add( 'simplebox', {
            button: 'Create a simple box'
        } );

    }
} );