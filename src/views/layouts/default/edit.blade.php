@extends('pub_theme::layouts.app')
@section('page_heading',trans($view.'.page_heading'))
@section('content')
@php
	
@endphp
@include('extend::includes.components')
@include('extend::includes.flash')
@include('extend::modal_ajax')
@include($view_default.'.btns.gear')
<div class="page-wrapper">
	{!! Theme::include('inner_page',['edit_type'=>$row_type],get_defined_vars() ) !!}
	{!! Theme::include('breadcrumb',[],get_defined_vars() ) !!}
	{!! Theme::include('parent_tabs',['tabs'=>$row->parent_tabs],get_defined_vars() ) !!}
	{!! Theme::include('tabs',['tabs'=>$row->tabs],get_defined_vars() ) !!}
	<section class="create-page inner-page">
		<div class="container">
			<div class="row">
				{!! Theme::include('header',	['edit_type'=>$row_type],get_defined_vars() ) !!}
				{!! Theme::include($view_body,	['edit_type'=>$row_type],get_defined_vars() ) !!}
				{!! Theme::include('footer',	['edit_type'=>$row_type],get_defined_vars() ) !!}
			</div>
		</div>
	</section>
</div>
@endsection
