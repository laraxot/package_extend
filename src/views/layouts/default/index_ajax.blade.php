@php
	$tabs=$row->tabs;
	$parent_tabs=$row->parent_tabs;
	$edit_type=snake_case($row->post_type);
@endphp
@include('extend::includes.components')
@include('extend::includes.flash')
@include('extend::modal_ajax')
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
	
	<section class="restaurants-page">
		<div class="container">
			<div class="row">
				@includeFirst(
					[
						$view_default.'.'.$view_body,
						$view_extend.'.'.$view_body,
					]
				)
			</div>
		</div> 
	</section>
	
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
	