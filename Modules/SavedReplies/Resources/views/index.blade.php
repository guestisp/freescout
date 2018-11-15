@extends('layouts.app')

@section('title_full', __('Saved Replies').' - '.$mailbox->name)

@section('body_attrs')@parent data-mailbox_id="{{ $mailbox->id }}"@endsection

@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

@section('content')

    <div class="section-heading">
        {{ __('Saved Replies') }}<a href="{{ route('mailboxes.saved_replies.ajax_html', ['action' => 'create']) }}" class="btn btn-primary margin-left new-saved-reply" data-trigger="modal" data-modal-size="lg" data-modal-title="{{ __('Create a New Saved Reply') }}" data-modal-no-footer="true" data-modal-on-show="initNewSavedReply">{{ __('New Saved Reply') }}</a>
    </div>
    @if (count($saved_replies))
	    <div class="row-container">
	    	<div class="col-md-11">
				<div class="panel-group accordion margin-top">
					@foreach ($saved_replies as $saved_reply)
				        <div class="panel panel-default" id="saved-reply-{{ $saved_reply->id }}">
				            <div class="panel-heading">
				                <h4 class="panel-title">
				                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse-{{ $saved_reply->id }}">
				                    	{{ $saved_reply->name }}
				                        <b class="caret"></b>
				                    </a>
				                </h4>
				            </div>
				            <div id="collapse-{{ $saved_reply->id }}" class="panel-collapse collapse">
				                <div class="panel-body">
									<form class="form-horizontal" method="POST" action="">

										<div class="form-group">
									        <label class="col-md-1 control-label">{{ __('Name') }}</label>

									        <div class="col-md-11">
									            <input class="form-control" name="name" maxlength="75" value="{{ $saved_reply->name }}" />
									        </div>
									    </div>

										<div class="form-group">
									        <label class="col-md-1 control-label">{{ __('Reply') }}</label>

									        <div class="col-md-11 saved-reply-editor">
									            <textarea class="form-control" name="text" rows="8">{{ $saved_reply->text }}</textarea>
									        </div>
									    </div>

										<div class="form-group margin-top margin-bottom-10">
									        <div class="col-md-11 col-md-offset-1">
									            <button type="button" class="btn btn-primary saved-reply-save" data-saved_reply_id="{{ $saved_reply->id }}" data-loading-text="{{ __('Saving') }}…">{{ __('Save Reply') }}</button> 
									            <a href="#" class="btn btn-link text-danger sr-delete-trigger" data-loading-text="{{ __('Deleting') }}…" data-saved_reply_id="{{ $saved_reply->id }}">{{ __('Delete') }}</a>
									        </div>
									    </div>
									</form>
				                </div>
				            </div>
				        </div>
				    @endforeach
			    </div>
			</div>
		</div>
	@else
		@include('partials/empty', ['icon' => 'comment', 'empty_header' => __("Save time with saved replies!"), 'empty_text' => __('A saved reply is a snippet of text that can be quickly added to the editor when replying to a customer.')])
	@endif
@endsection

@include('partials/editor')

@section('javascript')
    @parent
    initSavedReplies();
@endsection