@extends('pub_theme::layouts.app')
@section('content')
@php
	//ddd($view_default); //pub_theme::layouts.default.index_edit
	//$tabs_path='xra.tabs.'.str_slug(str_replace('.','-',$view));
	//ddd($tabs_path);
	//$tabs=config($tabs_path);
	//$second_last = collect(\array_slice($params, -2))->first();
	//ddd($second_last);
	 
	$tabs=$row->tabs;
	$parent_tabs=$row->parent_tabs;
	$edit_type=snake_case($row->post_type);
	
	//ddd($row->linkable);
	//ddd($parent_tabs);
	$view_body='';
	if(\View::exists($view.'.left') || \View::exists($view_default.'.left.'.$edit_type) ) {
		$view_body.='left_';
	}
	$view_body.='body';
	if(\View::exists($view.'.right') || \View::exists($view_default.'.right.'.$edit_type) ) {
		$view_body.='_right';
	}

	//$rows=$row->archive()->paginate(10);
	//$items=$rows

@endphp
	@include('extend::includes.components')
	@include('extend::includes.flash')
	@include('extend::modal_ajax')
	{{--  
	@includeFirst([$view_default.'.btns.gear',$view_extend.'.btns.gear'])
	--}}
		<div class="page-wrapper">
			@if(isset($step))
			@include('pub_theme::layouts.partials.top_links',['step'=>$step])
			@endif
			@if(is_array($parent_tabs) && is_object($second_last))
				
				@includeFirst(['pub_theme::layouts.default.show.inner_page.'.snake_case($second_last->post_type),'pub_theme::layouts.default.show.inner_page'])
			@else
				@includeFirst(
					[
						$view.'.inner_page',
						$view_default.'.inner_page',
						$view_extend.'.inner_page'
					]
				)
			@endif
			@include('pub_theme::layouts.partials.breadcrumb')
			@if(is_array($parent_tabs))
				@includeFirst([$view.'.parent_tabs',$view_default.'.parent_tabs',$view_extend.'.inner_page'],['tabs'=>$parent_tabs] )
			@endif
			@if(is_array($tabs))
				{{-- tabs1 solo per non leggere file vecchi --}}
				@includeFirst([$view.'.tabs1',$view_default.'.tabs',$view_extend.'.inner_page'],['tabs'=>$tabs] )
			@endif
			{{--
			@include($view.'.result_show')
			--}}
			<section class="restaurants-page">
				<div class="container">
					<div class="row">
						@includeFirst(
							[
								$view.'.header',
								$view_default.'.header.'.$edit_type,
								$view_extend.'.header.'.$edit_type,
								$view_default.'.header',
								$view_extend.'.header'
							],
							['edit_type'=>$edit_type]
						)
						  
						@includeFirst([$view_default.'.'.$view_body,$view_extend.'.'.$view_body])
						@includeFirst(
							[
								$view.'.footer',
								$view_default.'.footer.'.$edit_type,
								$view_extend.'.footer.'.$edit_type,
								$view_default.'.footer',
								$view_extend.'.footer'
							],
							['edit_type'=>$edit_type]
						)
					</div>
				</div>
			</section>
		</div>
@endsection		
