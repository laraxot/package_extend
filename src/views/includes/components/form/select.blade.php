@php
	$options=[];
	extract($attributes);
	//ddd($options);
	if(!isset($view)){
		$view=$comp_view;
	}
	$label=isset($attributes['label'])?$attributes['label']:trans($view.'.field.'.$name);
	$placeholder=trans($view.'.field.'.$name.'_placeholder');
@endphp

@component($blade_component,compact('name','value','attributes','comp_view'))
	@slot('label')
	{{ Form::label($name, $label , ['class' => 'control-label']) }}
	@endslot
	@slot('input')
		{{ Form::select($name,$options,$value,array_merge(['class' => 'form-control','placeholder'=>$placeholder] )) }}
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	@endslot
@endcomponent
