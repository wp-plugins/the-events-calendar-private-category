<?php
/**
* Events post main metabox
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>

<div class="form-field">
	<input type="checkbox" id="id_private" name="private" value="1" style="width: auto;"> <label for="id_private" style="display: inline;">Private Category</label>
</div>

<script type="text/javascript">
jQuery(function ($) {

	var checkAvailability = function() {

		var slugs_el = $('.wp-list-table td .hidden .slug');
		var titles_el = {};
		var slugs = [];
		
		for (var i=0; i<slugs_el.length; i++) {
			var slug = $(slugs_el[i]).text();
			slugs.push(slug);
			titles_el[slug] = $(slugs_el[i]).parents('td').find('.row-title').parent();
		}

		var data = {
			'action': 'private_category_check', 
			'category_slugs': slugs
		};
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function(data){
				$('.private-category-label').remove();
				
				if ((data.availabilities != undefined) && (data.category_slugs != undefined)) {
					for (var i=0; i<data.category_slugs.length; i++) {
						if (data.availabilities[data.category_slugs[i]]) {
							titles_el[data.category_slugs[i]].append('<span class="private-category-label"> - private</span>');
						}
					}
				}
				
			}
		});
	}

	var line_count = 0;

	var checkUpdate = function() {
		var new_line_count = $('.wp-list-table tbody tr').length;

		if (new_line_count != line_count) {
			line_count = new_line_count;
			checkAvailability();
		}
	}

	window.setInterval(checkUpdate, 1);
});
</script>