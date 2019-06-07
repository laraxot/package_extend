@php
	$parz=[];
	$parz['container0']=$row->post_type;
	$parz['item0']=$row->guid;
	$parz['container1']='rating';
	$rating_url=route('container0.container1.index_edit',$parz);
@endphp
<div class="row">
<div class="col-sm-8 col-md-8">
	@php
	$rating_avg=$row->ratings->avg('pivot.rating');
	$rating_count=$row->ratings->count();
	@endphp
	@include('extend::layouts.partials.rating.item',['label'=>'','rating_avg'=>$rating_avg,'rating_count'=>$rating_count])
</div>
<div class="col-sm-4 col-md-4">
	<button type="button" class="btn btn-red" data-toggle="modal" data-target="#myModalAjax" data-title="rate it" data-href="{{ $rating_url }}">
		<span class="font-white"><i class="fa fa-star"></i> Vota !</span>
	</button>
</div>
</div>