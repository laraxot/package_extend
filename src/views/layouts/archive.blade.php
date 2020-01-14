@extends('pub_theme::layouts.app')
@section('content')
@php
	$rows=$row->archive()->paginate(10);
@endphp
	@include('extend::includes.components')
	@include('extend::includes.flash')
		<div class="page-wrapper">
			@include('pub_theme::layouts.partials.top_links',['step'=>1])
			@include('pub_theme::layouts.partials.inner_page')
			@include('pub_theme::layouts.partials.breadcrumb_params')
			{{--
			@include($view.'.result_show')
			--}}
			<section class="restaurants-page">
				<div class="container">
					<div class="row">
						{{--
						@include($view.'.left',['restaurants'=>$restaurants])
						--}}
						<div class="col-xs-12 col-sm-7 col-md-7 col-lg-9">
							@foreach($rows as $key=>$row)
							@php
								$view1=$view.'.item.'.$row->post_type;
								if(!View::exists($view1)){
									$view1='pub_theme::layouts.partials.item.'.$row->post_type;
								}
							@endphp
							@include($view1,['key'=>$key,'row'=>$row])
							@endforeach
							{{ $rows->links('pub_theme::layouts.partials.pagination') }}
						</div>
					</div>
				</div>
			</section>
		</div>
@endsection		
