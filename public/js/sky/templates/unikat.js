/*
 Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.addTemplates("unikat", {
    imagesPath: '/js/sky/templates/images/',
    templates: [
        {
            title: 'Newsletter 2 Columns Image Gallery',
            image: 'newsletter-2-columns.png',
            description: 'A template which defines two columns each with an image and title.',
            html:
                '<table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateColumns">' +
                    '<tr>' +
                        '<td align="center" valign="top" class="templateColumnContainer" style="padding-top:20px;">' +
                            '<table border="0" cellpadding="20" cellspacing="0" width="100%">' +
                                '<tr>' +
                                    '<td class="leftColumnContent">{IMAGE}</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td valign="top" class="leftColumnContent">' +
                                        '<p>Title</p>' +
                                    '</td>' +
                                '</tr>' +
                            '</table>' +
                        '</td>'+
                        '<td align="center" valign="top" class="templateColumnContainer" style="padding-top:20px;">' +
                            '<table border="0" cellpadding="20" cellspacing="0" width="100%">' +
                                '<tr>' +
                                    '<td class="rightColumnContent">{IMAGE}</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td valign="top" class="rightColumnContent">' +
                                        '<p>Title</p>' +
                                    '</td>' +
                                '</tr>' +
                            '</table>' +
                        '</td>'+
                    '</tr>' +
                '</table>',
        },
    ]
});
