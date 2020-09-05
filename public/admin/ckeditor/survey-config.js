/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For the complete reference:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config
    config.language = 'fr';
    config.pasteFromWordPromptCleanup = true;
    config.pasteFromWordRemoveFontStyles = true;
    config.forcePasteAsPlainText = true;
    config.ignoreEmptyParagraph = true;
    config.removeFormatAttributes = true;
    // The toolbar groups arrangement, optimized for two toolbar rows.
    /*config.toolbarGroups = [
        //{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
        { name: 'links' },
        { name: 'insert' },
        //{ name: 'forms' },
        { name: 'tools' },
        //{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others' },
        '/',
        //{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
        { name: 'styles' },
        { name: 'colors' }
        //{ name: 'about' }
    ];*/
    //config.enterMode = CKEDITOR.ENTER_BR;
    config.toolbar = [
        { name: 'document', groups: [ 'mode', 'document', 'doctools','tools' ], items: [ 'Preview'/*, '-', 'Templates'*/ , 'Maximize', 'ShowBlocks'] },
        //{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
        '/',
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
        { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
        '/',
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
        //{ name: 'insert', items: [ 'Image'] },

        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align'/*, 'bidi'*/ ], items: [ 'NumberedList', 'BulletedList', '-',/* 'Outdent', 'Indent', '-',*/ 'Blockquote', /*'CreateDiv',*/ '-', 'JustifyLeft', 'JustifyCenter', /*'JustifyRight',*/ 'JustifyBlock'/*, '-', 'BidiLtr', 'BidiRtl'*/ ] },
        { name: 'styles', items: [ 'Format'] }
    ];

    config.height = '450px';//hauteur fenêtre
    config.resize_dir = 'vertical';

    // Remove some buttons, provided by the standard plugins, which we don't
    // need to have in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript';
};
