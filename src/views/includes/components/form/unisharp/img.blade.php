@php
	Theme::add('/vendor/laravel-filemanager/js/lfm.js');
	Theme::add('/vendor/laravel-filemanager/css/lfm.css');
	//Theme::add('backend::includes/components/form/unisharp/js/uploadimg.js');
	Theme::add(str_replace('.','/',$comp_view).'/js/uploadimg.js');
	//ddd($comp_view);//extend::includes.components.form.unisharp.img
	$src=Form::getValueAttribute($name);
	if($src=='') $src='/images/nophoto.png';
	$label=isset($attributes['label'])?$attributes['label']:trans($view.'.field.'.$name);
	$placeholder=trans($view.'.field.'.$name.'_placeholder');
@endphp

@component($blade_component,compact('name','value','attributes','lang','comp_view'))
	@slot('label')
		{{ Form::label($name, $label , ['class' => 'control-label']) }}
	@endslot
	@slot('input')	
		<span class="input-group-btn">
			<img id="holder" style="margin-top:15px;max-height:100px;" src="{{ $src }}">
			<a data-input="{{ $name }}" data-preview="holder" class="btn btn-secondary" id="lfm">
				<i class="fa fa-picture-o"></i> Choose
			</a>
		</span>
		{{ Form::text($name, $value, array_merge(['id'=>$name, 'class' => 'form-control'], $attributes)) }}
	@endslot
@endcomponent
