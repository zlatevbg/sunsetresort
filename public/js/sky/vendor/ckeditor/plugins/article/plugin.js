CKEDITOR.plugins.add('article', {

	icons: 'article',

	init: function(editor) {

		editor.addCommand('insertArticle', {
			exec: function(editor) {
				var selection = editor.getSelection();
                var node = selection.getStartElement();
                var ascendant = node.getAscendant('article');
                var element = CKEDITOR.dom.element.createFromHtml(
                    '<article class="company-info">' +
                        '<h1>Title</h1>' +
                        '<div class="company-info-image">Image</div>' +
                        '<div class="text"><p>Text</p></div>' +
                    '</article>'
                );
                element.insertAfter(ascendant);
			}
		});

		editor.ui.addButton('Article', {
			label: 'Insert Article',
			command: 'insertArticle',
			toolbar: 'insert'
		});

        editor.on('instanceReady', function(e) {
            if ($(e.editor.getData()).filter('.company-info-wrapper').length) {
                editor.commands.insertArticle.enable();
            } else {
                editor.commands.insertArticle.disable();
            }
        });

        editor.on('change', function(e) {
            if ($(e.editor.getData()).filter('.company-info-wrapper').length) {
                editor.commands.insertArticle.enable();
            } else {
                editor.commands.insertArticle.disable();
            }
        });
	}
});
