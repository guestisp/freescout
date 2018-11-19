<div id="conv_tags">
	@foreach($tags as $tag)
		<span class="tag"><a class="tag-name" href="{{ $tag->getUrl() }}" target="_blank">{{ $tag->name }}</a> <a class="tag-remove" href="#" title="{{ __('Remove Tag') }}">×</a></span>
	@endforeach
</div>