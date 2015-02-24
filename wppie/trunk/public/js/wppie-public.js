(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 */
	$(document).ready(function() {
		var items = $('.wppie.item'),
			pager = $('.wppie.pager');

		if (pager.length > 0) {
			pager.pagination({
				items: items.length,
				itemsOnPage: 10,
				cssStyle: 'compact-theme',
				onInit: function() {
					//get linked-to page number
					var page = document.location.hash;
					page = parseInt(page.substring(page.indexOf('-')+1));
					page = isNaN(page) ? 1 : page;

					pager.pagination('selectPage', page);
				},
				onPageClick: function(page) {
					var shownMin = (page - 1) * this.itemsOnPage;
					items.hide();

					for (var i = 0; i < this.itemsOnPage; i++) {
						if (shownMin + i === this.items) { break; }
						$(items[shownMin + i]).show();
					}

					// Chrome tries to download the whole thing and chokes on this many
					// items, so no metadata loading.
					// $('audio:visible[preload="none"]', items).attr('preload', 'metadata');
				}
			});
		}
	});

})( jQuery );
