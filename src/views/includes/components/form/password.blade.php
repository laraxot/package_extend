@php
	if(isset($attributes['label']))
		$label=$attributes['label'];
	else
		$label=trans($lang.'.'.$name);
@endphp
<div class="form-group{{ $errors->has($name) ? ' has-error' : '' }}">
	{{ Form::label($name, $label , ['class' => 'control-label']) }}
	{{ Form::password($name,  array_merge(['class' => 'form-control','placeholder'=>trans($lang.'.'.$name.'_placeholder')], $attributes)) }}
	{{--  <input type="password" class="form-control" id="{{ $name }}" name="{{ $name }}" placeholder="{{ trans($lang.'.'.$name.'_placeholder') }}"> --}}
	@if ( $errors->has($name) )
		<span class="help-block">
			<strong>{{ $errors->first($name) }}</strong>
		</span>
	@endif
	<small class="form-text text-muted">{{ trans($lang.'.'.$name.'_help') }} </small> 
</div>
