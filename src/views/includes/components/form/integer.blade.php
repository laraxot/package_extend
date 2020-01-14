<div class="form-group{{ $errors->has($name) ? ' has-error' : '' }}">
	{{ Form::label($name,  trans($view.'.field.'.$name), ['class' => 'col-md-4 control-label']) }}
	<div class="col-md-6">
		<div class="datepicker-input input-group date">
		{{ Form::number($name, $value, array_merge(['class' => 'form-control'], $attributes)) }}
		<span class="input-group-addon">
            <span {{--class="glyphicon glyphicon-calendar"--}}></span>
        </span>
        </div>
		@if ( $errors->has($name) )
			<span class="help-block">
				<strong>{{ $errors->first($name) }}</strong>
			</span>
		@endif
	</div>
</div>