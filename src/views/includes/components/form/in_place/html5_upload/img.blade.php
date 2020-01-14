@php
	//Theme::add('extend::includes.components.form.html5upload/js/html5imageupload.js');
	//Theme::add('extend::includes.components.form.html5upload/css/html5imageupload.css');
	//Theme::add('extend::includes.components.form.html5upload/css/glyphicons.css');
	//Theme::add('extend::includes.components.form.html5upload/css/style.css');
	Theme::add($comp_view.'/js/html5imageupload.js');
	Theme::add($comp_view.'/css/html5imageupload.css');
	Theme::add($comp_view.'/css/glyphicons.css');
	Theme::add($comp_view.'/css/style.css'); 


	$label=isset($attributes['label'])?$attributes['label']:trans($view.'.field.'.$name);
	$placeholder=isset($attributes['placeholder'])?$attributes['placeholder']:trans($view.'.field.'.$name.'_placeholder');
	$val=Form::getValueAttribute($name);
	$model=Form::getModel();

@endphp

<div class="form-group">
    <label>{{ $label }}</label>
    <div class="dropzone" data-width="400" data-height="400" data-url="{{ route('imgz.canvas') }}"  style="width: 100%;" data-image="{{ $val }}" data-field="{{ $name }}" data-updateurl="{{ $model->update_url }}">
     	{{ Form::file($name.'_thumb', $value, array_merge(['id'=>$name.'_thumb', 'class' => 'form-control'], $attributes)) }}
	</div>
	{{ Form::text($name, $value, array_merge(['id'=>$name,'class' => 'form-control','placeholder'=>$placeholder], $attributes)) }}
	@if ( $errors->has($name) )
		<span class="help-block">
			<strong>{{ $errors->first($name) }}</strong>
		</span>
	@endif
</div>
@push('scripts')
<script>
$(function () {
	$.ajaxSetup({
    	headers: {
        	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    	}
	});
	$('.dropzone').html5imageupload({
		onAfterProcessImage: function() {
			var $val=$(this.element).data('name');
    		var $field = $(this.element).data('field');
			var $token = $('meta[name="csrf-token"]').attr('content');
			var $data = { _method: 'put', _token: $token };
			var $ajax_url=$(this.element).data('updateurl');
			$data[$field] = $val;
			$('#{{ $name }}').val($val);
			$.ajax({
		        url: $ajax_url,
		        type: 'post',
		        dataType: 'json',
		        data: $data,
		    }).done(function (data) {
		        //$this.replaceWith(viewableText);
		        //viewableText.click(editInPlace);
		        //modal.find('.form-msg').html('<div class="alert alert-success"><strong>Success </strong>'+data.msg+'</div>');
		    }).fail(function (response) {
		        //console.log(response.responseText);
		        //$this.replaceWith(response.responseText);
		    });
		},
		onAfterCancel: function() {
			$('#{{ $name }}').val('');
		}
	});
});
</script>
@endpush	    