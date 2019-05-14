@extends('pub_theme::layouts.app')
@section('page_heading',trans($view.'.page_heading'))
@section('content')
@php
	//ddd($second_last);
	//ddd($item0->restaurants);
	//ddd($row);
	$tabs=$parent_tabs=$edit_type=null;
	if(is_object($row)){
		$tabs=$row->tabs;
		$parent_tabs=$row->parent_tabs;
		$edit_type=snake_case($row->post_type);
	}
	if($edit_type==null && is_object($second_last)){
		$edit_type=snake_case($second_last->post_type);
	}



	$view_body='';
	if(\View::exists($view.'.left') || \View::exists($view_default.'.left.'.$edit_type) ) {
		$view_body.='left_';
	}
	$view_body.='body';
	if(\View::exists($view.'.right') || \View::exists($view_default.'.right.'.$edit_type) ) {
		$view_body.='_right';
	}

@endphp
@include('extend::includes.components')
@include('extend::includes.flash')
@include('extend::modal_ajax')
@include($view_default.'.btns.gear')
<div class="page-wrapper">
	@includeFirst([$view.'.inner_page',$view_default.'.inner_page.'.$edit_type,$view_default.'.inner_page'],['edit_type'=>$edit_type])
	@include('pub_theme::layouts.partials.breadcrumb_params')
	@if(is_array($parent_tabs))
		@includeFirst([$view.'.parent_tabs',$view_default.'.parent_tabs'],['tabs'=>$parent_tabs] )
	@endif
	@if(is_array($tabs))
		{{-- tabs1 solo per non leggere file vecchi --}}
		@includeFirst([$view.'.tabs1',$view_default.'.tabs'],['tabs'=>$tabs] )
	@endif
	<section class="create-page inner-page">
		<div class="container">
			<div class="row">
				{{--
				<div class="col-md-8">
					<div class="widget">
						<div class="widget-body">
							@if (count($errors) > 0)
							<div class="alert alert-danger">
								<ul>
									@foreach ($errors->all() as $k=>$error)
									<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
							@endif
							@includeFirst([$view.'.form',$view_default.'.form.'.snake_case($row->post_type)])
						</div>
					</div>
				</div>
				<div class="col-md-4">
					@includeFirst([$view.'.right',$view_default.'.right'])
				</div>
				--}}
				@include($view_default.'.'.$view_body,['edit_type'=>$edit_type])
			</div>
		</div>
	</section>
</div>
@endsection
