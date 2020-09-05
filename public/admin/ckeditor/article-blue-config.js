/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For the complete reference:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config
    config.language = 'fr';

    config.extraPlugins = 'magicline,showblocks,wordcount,panelbutton,colorbutton';

    //config.resize_enabled = false;
    //config.removePlugins = 'resize',
    // The toolbar groups arrangement, optimized for two toolbar rows.
    /*config.toolbarGroups = [

     //{ name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },

     { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
     { name: 'insert' },
     { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
     { name: 'links' },
     '/',
     { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
     { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
     { name: 'forms' },
     { name: 'styles' },
     { name: 'colors' },
     { name: 'tools' },
     { name: 'others' }

     //{ name: 'about' }
     ];*/

    //config.enterMode = CKEDITOR.ENTER_BR;
    config.pasteFromWordPromptCleanup = true;
    config.pasteFromWordRemoveFontStyles = true;
    config.forcePasteAsPlainText = true;
    config.ignoreEmptyParagraph = true;
    config.removeFormatAttributes = true;
    //config.autoParagraph = true;

    // Don't work
    // config.filebrowserUploadUrl = 'http://reserver-chic.dev/app_dev.php/admin/upload' ;
    config.toolbar = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'TextColor', '-', 'Subscript', 'Superscript'] },
        { name: 'links', items: [ 'Link', 'Unlink'] },
        { name: 'insert', items: [ 'Image', 'Table'/*, 'Iframe'*/ ] },

        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align'/*, 'bidi'*/ ], items: [ 'NumberedList', 'BulletedList', 'CreateDiv','-',/* 'Outdent', 'Indent', '-',*/  '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'/*, '-', 'BidiLtr', 'BidiRtl'*/ ] },
        { name: 'styles', items: [ 'Format'] },
        { name: 'sources', items: ['Source'] }
    ];
    config.height = '300px';//hauteur fenêtre
    config.resize_dir = 'vertical';

    config.wordcount = {

        // Whether or not you want to show the Word Count
        showWordCount: true,

        // Whether or not you want to show the Char Count
        showCharCount: true,

        // Option to limit the characters in the Editor
        charLimit: 'unlimited',

        // Whether or not to include Html chars in the Char Count
        countHTML: false,

        // Option to limit the words in the Editor
        wordLimit: 'unlimited'
    };

    config.contentsCss = '/css/wysiwyg-blue.css?2';
};
