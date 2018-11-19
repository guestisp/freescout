/**
 * Module's JavaScript.
 */

// Saved Replies button in reply editor
var EditorSavedRepliesButton = function (context) {
	var ui = $.summernote.ui;

 	var items = [];
 	$('.sr-dropdown-list:first li').each(function(i, el) {
 		items.push({
 			value: $(el).attr('data-id'),
	        text: $(el).text()
 		});
 	});

	// create button
	var button = ui.buttonGroup([
		// We have to create button inside button group to have tooltip separate for button
	    ui.button({
	        className: 'dropdown-toggle',
	        contents: '<i class="glyphicon glyphicon-comment"></i>',
	        tooltip: Lang.get("messages.saved_replies"),
	        container: 'body',
	        data: {
	            toggle: 'dropdown'
	        }
	    }),
	    ui.dropdown({
	        className: 'dropdown-saved-replies',
	        //checkClassName: ui.options.icons.menuCheck,
	        items: items,
	        template: function (item) {
	            return item.text;
	        },
	        click: function(e) {
	        	
	        	if (typeof(e.target) == "undefined") {
	        		return;
	        	}

	        	var saved_reply_id = $(e.target).attr('data-value');
	        	if (saved_reply_id) {
	        		// Load saved reply
	        		fsAjax({
							action: 'get',
							saved_reply_id: saved_reply_id,
							conversation_id: getGlobalAttr('conversation_id')
						}, 
						laroute.route('mailboxes.saved_replies.ajax'), 
						function(response) {
							if (typeof(response.status) != "undefined" && response.status == 'success' &&
								typeof(response.text) != "undefined" && response.text) 
							{
								context.invoke('editor.pasteHTML', response.text);
								//$('#body').summernote('pasteHTML', response.text);
								$('.form-reply:visible:first :input[name="saved_reply_id"]:first').val(saved_reply_id);
							} else {
								showAjaxError(response);
							}
							loaderHide();
						}
					);
	        	} else {
	        		// Save this reply
	        		showModal({
	        			'remote': laroute.route('mailboxes.saved_replies.ajax_html', {'action': 'create'}),
	        			'size': 'lg',
	        			'title': Lang.get("messages.new_saved_reply"),
	        			'no_footer': true,
	        			'on_show': 'showSaveThisReply'
	        		});
	        	}

	        	e.preventDefault();
	        }
	    })
	]);

	var obj = button.render();

	// Add divider
	obj.children().find('a[data-value="divider"]').parent().addClass('divider').children().first().remove();

	return obj;
}

fs_conv_editor_buttons['savedreplies'] = EditorSavedRepliesButton;
fs_conv_editor_toolbar[0][1].push('savedreplies');

function initSavedReplies()
{
	$(document).ready(function(){
		summernoteInit('.saved-reply-text', {minHeight: 250});

		// Update saved reply
		$('.saved-reply-save').click(function(e) {
			var saved_reply_id = $(this).attr('data-saved_reply_id');
			var name = $('#saved-reply-'+saved_reply_id+' :input[name="name"]').val();
			var button = $(this);
	    	button.button('loading');
			fsAjax({
					action: 'update',
					saved_reply_id: saved_reply_id,
					name: name,
					text: $('#saved-reply-'+saved_reply_id+' :input[name="text"]').val()
				}, 
				laroute.route('mailboxes.saved_replies.ajax'), 
				function(response) {
					if (typeof(response.status) != "undefined" && response.status == 'success' &&
						typeof(response.msg_success) != "undefined")
					{
						showFloatingAlert('success', response.msg_success);
						$('#saved-reply-'+saved_reply_id+' .panel-title a:first').text(name);
					} else {
						showAjaxError(response);
					}
					button.button('reset');
					loaderHide();
				}
			);
		});

		// Delete saved reply
		$(".sr-delete-trigger").click(function(e){
			var button = $(this);

			showModalConfirm(Lang.get("messages.confirm_delete_saved_reply"), 'sr-delete-ok', {
				on_show: function(modal) {
					var saved_reply_id = button.attr('data-saved_reply_id');
					modal.children().find('.sr-delete-ok:first').click(function(e) {
						button.button('loading');
						modal.modal('hide');
						fsAjax(
							{
								action: 'delete',
								saved_reply_id: saved_reply_id
							}, 
							laroute.route('mailboxes.saved_replies.ajax'), 
							function(response) {
								showAjaxResult(response);
								button.button('reset');
								$('#saved-reply-'+saved_reply_id).remove();
							}
						);
					});
				}
			}, Lang.get("messages.delete"));
			e.preventDefault();
		});
	});
}

// Create saved reply
function initNewSavedReply(jmodal)
{
	$(document).ready(function(){
		// Show text
		summernoteInit('.modal-dialog .new-saved-reply-editor:visible:first textarea:first', {minHeight: 250});

		// Process save
		$('.modal-content .new-saved-reply-save:first').click(function(e) {
			var button = $(this);
	    	button.button('loading');
	    	var name = $(this).parents('.modal-content:first').children().find(':input[name="name"]').val();
	    	var text = $(this).parents('.modal-content:first').children().find(':input[name="text"]').val();
			fsAjax({
					action: 'create',
					mailbox_id: getGlobalAttr('mailbox_id'),
					from_reply: getGlobalAttr('conversation_id'),
					name: name,
					text: text
				}, 
				laroute.route('mailboxes.saved_replies.ajax'), 
				function(response) {
					if (typeof(response.status) != "undefined" && response.status == 'success' &&
						typeof(response.id) != "undefined" && response.id)
					{

						if (typeof(response.msg_success) != "undefined" && response.msg_success) {
							// Show alert (in conversation)
							jmodal.modal('hide');
							showFloatingAlert('success', response.msg_success);
							loaderHide();

							// Add newly created saved reply to the list
							var li_html = '<li><a href="#" data-value="'+response.id+'">'+htmlEscape(name)+'</a></li>';
							$('.form-reply:first:visible .dropdown-saved-replies:first').children('li:last').prev().before(li_html);
						} else {
							// Reload page (in saved replies list)
							window.location.href = '';
						}
					} else {
						showAjaxError(response);
						loaderHide();
						button.button('reset');
					}
				}
			);
		});
	});
}

// Display modal and show reply text
function showSaveThisReply(jmodal)
{
	// Show text
	$('.modal-dialog .new-saved-reply-editor:visible:first textarea[name="text"]:first').val(getReplyBody());
	initNewSavedReply(jmodal);
}