/**
 * Module's JavaScript.
 */
function initConvTags(remove_title)
{
	$(document).ready(function(){
		// Add tag
		$('#add-tag-wrap input').on('keyup', function(e) {
			if(e.keyCode == 13) {
		        addTag(remove_title);
		    }
		});
		$('#add-tag-wrap .btn').click(function(e) {
			addTag(remove_title);
			e.preventDefault();
			e.stopPropagation();
		});

		// Remove tag
		$('#conv_tags .tag-remove').click(function(e) {
			var tag_name = $(this).parent().children('.tag-name:first').text();
			
			$(this).parent().remove();
			fsAjax({
					action: 'remove',
					tag_name: tag_name,
					conversation_id: getGlobalAttr('conversation_id')
				}, 
				laroute.route('tags.ajax'), 
				function(response) {
					if (typeof(response.status) != "undefined" && response.status == 'success') {
						// Do nothing
					} else {
						showAjaxError(response);
					}
				},
				true
			);
			e.preventDefault();
		});		
	});
}

function addTag(remove_title)
{
	var input = $('#add-tag-wrap input:first');
	var button = $('#add-tag-wrap .btn:first');

	input.attr('disabled', 'disabled');
	button.attr('disabled', 'disabled');
	//button.button('loading');

	var tag_names = input.val().split(',');

	if (!tag_names.length) {
		return;
	}
	for (i in tag_names) {
		tag_names[i] = tag_names[i].trim();
	}

	// Check if there is already such tag
	$('#conv_tags .tag-name').each(function(i, el) {
		if ($(el).text() == tag_names[i]) {
			delete tag_names[i];
		}
	});

	if (!tag_names.length) {
		return;
	}

	fsAjax({
			action: 'add',
			tag_names: tag_names,
			conversation_id: getGlobalAttr('conversation_id')
		}, 
		laroute.route('tags.ajax'), 
		function(response) {
			if (typeof(response.status) != "undefined" && response.status == 'success' &&
				typeof(response.tags) != "undefined" && response.tags
			) {
				// Show tags
				var html = '';
				for (i in response.tags) {
					tag = response.tags[i];
					html += '<span class="tag"><a class="tag-name" href="'+tag.url+'" target="_blank">'+htmlEscape(tag.name)+'</a> <a class="tag-remove" href="#" title="'+remove_title+'">×</a></span>';
				}
				$('#conv_tags').append(html);
			} else {
				showAjaxError(response);
			}
			input.removeAttr('disabled').val('');
			button.removeAttr('disabled');
		},
		true,
		function(response) {
			showFloatingAlert('error', Lang.get("messages.ajax_error"));
			input.removeAttr('disabled').val('');
			button.removeAttr('disabled');
		}
	);
}