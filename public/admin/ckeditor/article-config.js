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

    // Make dialogs simpler.
    config.removeDialogTabs = 'image:advanced;image:Link;link:advanced;link:upload';
    config.linkShowTargetTab = true;

    config.enterMode = CKEDITOR.ENTER_P;
    config.pasteFromWordPromptCleanup = true;
    config.pasteFromWordRemoveFontStyles = true;
    config.forcePasteAsPlainText = true;
    config.ignoreEmptyParagraph = true;
    config.removeFormatAttributes = true;
    //config.autoParagraph = true;

    // Don't work
    config.filebrowserBrowseUrl = '/browser';
    config.filebrowserUploadUrl = '/upload' ;
    config.toolbar = [
        { name: 'Source', groups: [ 'Source' ], items: [ 'Source'] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline'] },
        { name: 'links', items: [ 'Link', 'Unlink'] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align' ], items: [ 'NumberedList', 'BulletedList','-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
        { name: 'styles', items: [ 'Styles' ] }
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

    config.contentsCss = '/bundles/admin/css/main.css';
};

CKEDITOR.on('dialogDefinition', function(ev) {
    // Take the dialog name and its definition from the event data.
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;

    // Check if the definition is from the dialog we're
    // interested in (the 'link' dialog).
    if (dialogName == 'link') {
        // Get a reference to the 'Link Info' tab.
        var infoTab = dialogDefinition.getContents('info');
        // Remove unnecessary widgets from the 'Link Info' tab.
        infoTab.remove('linkType');
        infoTab.remove('browse');

        /*var advancedTab = dialogDefinition.getContents('advanced');
        infoTab.remove('')*/
    }
});

