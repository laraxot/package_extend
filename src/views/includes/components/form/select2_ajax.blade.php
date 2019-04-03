@php
	Theme::addSelect2();
	if(isset($attributes['label']))
		$label=$attributes['label'];
	else
		$label=trans($lang.'.'.$name);
	$placeholder=trans($lang.'.'.$name.'_placeholder');
	$ajaxurl=url($attributes['ajaxurl'])
@endphp
<div class="form-group{{ $errors->has($name) ? ' has-error' : '' }}">
	{{ Form::label($name, $label , ['class' => 'control-label']) }}
	<select class="form-control select2ajax" name="{{ $name }}" data-tags="true" 
        					data-placeholder="{{ $placeholder }}" 
        					data-allow-clear="true" data-ajax--url="{{ $ajaxurl }}" data-ajax--cache="true" >
    </select>
    @if ( $errors->has($name) )
		<span class="help-block">
			<strong>{{ $errors->first($name) }}</strong>
		</span>
	@endif
	<small class="form-text text-muted">{{ trans($lang.'.'.$name.'_help') }} </small>
</div>