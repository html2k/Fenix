/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */


CKEDITOR.disableAutoInline = true;
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.

    window.CONFIG_CKEDITOR.push('filemanager');
    config.extraPlugins = window.CONFIG_CKEDITOR.join(',');
    config.contentEditable = false;
};
