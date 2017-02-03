// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Html tags
// http://en.wikipedia.org/wiki/html
// ----------------------------------------------------------------------------
// Basic set. Feel free to add more tags
// ----------------------------------------------------------------------------
function launchFileBrowser(type) {
	$.fancybox({
		'autoSize': false,
		'width': 970,
		'height': 450,
		'padding': 10,
		'margin': 0,
		'centerOnScroll': true,
		'type': 'iframe',
		'href': '/admin/index.php/filebrowser/index/' + type
	});
}

mySettings = {
	onShiftEnter:	{keepDefault:false, replaceWith:'<br />\n'},
	onCtrlEnter:	{keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
	onTab:			{keepDefault:false, openWith:'	 '},
	resizeHandle:	false,
	markupSet: [
		{name:'Heading 1', key:'1', openWith:'<h1(!( class="[![Class]!]")!)>', closeWith:'</h1>', placeHolder:'Your title here...' },
		{name:'Heading 2', key:'2', openWith:'<h2(!( class="[![Class]!]")!)>', closeWith:'</h2>', placeHolder:'Your title here...' },
		{name:'Heading 3', key:'3', openWith:'<h3(!( class="[![Class]!]")!)>', closeWith:'</h3>', placeHolder:'Your title here...' },
		{separator:'---------------' },
		{name:'Bold', key:'B', openWith:'(!(<strong>|!|<b>)!)', closeWith:'(!(</strong>|!|</b>)!)' },
		{name:'Italic', key:'I', openWith:'(!(<em>|!|<i>)!)', closeWith:'(!(</em>|!|</i>)!)' },
		{name:'Stroke through', key:'S', openWith:'<del>', closeWith:'</del>' },
		{separator:'---------------' },
		{name:'Ul', openWith:'<ul>\n', closeWith:'</ul>\n' },
		{name:'Ol', openWith:'<ol>\n', closeWith:'</ol>\n' },
		{name:'Li', openWith:'<li>', closeWith:'</li>' },
		{separator:'---------------' },
		{name:'Image', className: 'tinyBrowserImage', 
			beforeInsert: function(h){
				launchFileBrowser('image');
			}
		},
		{name:'File', 
			beforeInsert: function(h){
				launchFileBrowser('file');
			}
		},
		{name:'Link', key:'L', openWith:'<a href="[![Link:!:http://]!]"(!( title="[![Title]!]")!)>', closeWith:'</a>', placeHolder:'Your text to link...' },
		{separator:'---------------' },
		{name:'Preview', className:'preview',
			afterInsert: function(h) {
				var thiseditor = $(h.textarea).attr('id');
				var thiseditor_content = $(h.textarea).val();

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: "/admin/assets/scripts/markitup/templates/preview.php",
					data	: 'content='+thiseditor_content,
					success: function(data) {
						$.fancybox(data, {
					    	'autoSize': false,
							'width': 600,
							'height': 350,
							'padding': 10,
							'margin': 0,
							'centerOnScroll': true,
						});
					}
				});
			}
		}
	]
},
imageOnly = {
    onShiftEnter:    {keepDefault:false, replaceWith:'<br />\n'},
    onCtrlEnter:     {keepDefault:false, openWith:'\n<p>', closeWith:'</p>\n'},
    onTab:           {keepDefault:false, openWith:'     '},
    resizeHandle:	 false,
    markupSet:  [
		{name:'Image', className: 'tinyBrowserImage', 
			beforeInsert: function(h){
				launchFileBrowser('image');
			}
		},
		{name:'Preview', className:'preview',
			afterInsert: function(h) {
				var thiseditor = $(h.textarea).attr('id');
				var thiseditor_content = $(h.textarea).val();

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: "/admin/assets/scripts/markitup/templates/preview.php",
					data	: 'content='+thiseditor_content,
					success: function(data) {
						$.fancybox(data, {
					    	'autoSize': true,
							'width': 600,
							'height': 350,
							'padding': 10,
							'margin': 0,
							'centerOnScroll': true,
						});
					}
				});
			}
		}
    ]
}