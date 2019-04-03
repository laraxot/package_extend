@php
	$label=isset($attributes['label'])?$attributes['label']:trans($lang.'.'.$name);
	$placeholder=trans($lang.'.'.$name.'_placeholder');
	Theme::add('theme/bc/typeahead.js/dist/typeahead.bundle.js');
	//Theme::add('backend::js/bsTypeahead.js');
	Theme::add(str_replace('.','/',$comp_view).'/js/bsTypeahead.js');
@endphp

@component($blade_component,compact('name','value','attributes','lang','comp_view'))
	@slot('label')
		{{ Form::label($name,   trans($lang.'.'.$name), ['class' => 'control-label']) }} {{-- sr-only  --}}
	@endslot
	@slot('input')
		<div class="form-group search_container">
			{{ Form::text($name, $value, array_merge([
						'class' => 'form-control form-control-lg typeahead search-input'
						,'placeholder'=> trans($lang.'.'.$name)
						], $attributes)) }}
		</div>
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	@endslot
@endcomponent

 
