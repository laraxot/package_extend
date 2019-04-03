@php
	$label=isset($attributes['label'])?$attributes['label']:trans($lang.'.'.$name);
	$placeholder=trans($lang.'.'.$name.'_placeholder');
@endphp

@component($blade_component,compact('name','value','attributes','lang','comp_view'))
	@slot('label')
		{{ Form::label($name, $label , ['class' => 'control-label']) }}
	@endslot
	@slot('input')
		{{ Form::text($name, $value, array_merge(['class' => 'form-control','placeholder'=>$placeholder], $attributes)) }}
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	@endslot
@endcomponent