@php
	Theme::add($comp_view.'/bc/clockpicker/dist/jquery-clockpicker.min.css');
	Theme::add($comp_view.'/bc/clockpicker/dist/jquery-clockpicker.min.js');
	Theme::add($comp_view.'/js/clockpicker.js');
	$label=isset($attributes['label'])?$attributes['label']:trans($view.'.field.'.$name);
	$placeholder=trans($view.'.field.'.$name.'_placeholder');
@endphp

@component($blade_component,compact('name','value','attributes','lang','comp_view'))
	@slot('label')
		{{ Form::label($name, $label , ['class' => 'control-label']) }}
	@endslot
	@slot('input')
<div class="input-group clockpicker">
    {{ Form::text($name, $value, array_merge(['class' => 'form-control','placeholder'=>$placeholder,'autocomplete'=>'off'], $attributes)) }}
    <span class="input-group-addon">
        <span class="glyphicon glyphicon-time"></span>
    </span>
</div>
@if ( $errors->has($name) )
	<span class="help-block">
		<strong>{{ $errors->first($name) }}</strong>
	</span>
@endif
	@endslot
@endcomponent
