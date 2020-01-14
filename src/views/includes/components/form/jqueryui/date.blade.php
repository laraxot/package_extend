@php
	$label=isset($attributes['label'])?$attributes['label']:trans($view.'.field.'.$name);
	$placeholder=trans($view.'.field.'.$name.'_placeholder');
	Theme::addScript('https://code.jquery.com/ui/1.12.1/jquery-ui.js');
	Theme::addStyle('https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
	Theme::addScript('theme/bc/jquery-ui/ui/i18n/datepicker-it.js');
	Theme::add($comp_view.'/js/jqui_date.js');
@endphp
{{-- [{{  \Route::currentRouteName() }}] container0.create --}}
{{-- {{ $view_name }} extend::includes.components.form.text --}}
{{--{{ $view }}--}}
@component($blade_component,compact('name','value','attributes','comp_view'))
	@slot('label')
		{{ Form::label($name, $label , ['class' => 'control-label']) }}
	@endslot
	@slot('input')
		<div class='input-group'>
			{{ Form::text($name, $value, array_merge(['class' => 'form-control date-picker-jqui','placeholder'=>$placeholder], $attributes)) }}
			<span class="input-group-append">
            	<span class="input-group-text">
            		<i class=" glyphicon glyphicon-calendar fa fa-calendar"></i>
            	</span>
        	</span>
        </div>
        {{-- $comp_view extend::includes.components.form.jqueryui.date --}}
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	@endslot
@endcomponent