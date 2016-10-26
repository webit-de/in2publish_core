function markdownToContainer(data) {
	'use strict';
	var markdown = marked(data);
	$('#changelogbox').html(markdown);
}

$.get('https://raw.githubusercontent.com/in2code-de/in2publish_core/feature/ffs/CHANGELOG.md', function() {
})
.done(function(data) {
	'use strict';
	markdownToContainer(data);
})
.fail(function() {
	'use strict';
	markdownToContainer('<h1>Changelog loading failed</h1>');
});
