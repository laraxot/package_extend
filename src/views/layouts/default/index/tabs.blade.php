<div class="menu-rest">
	@php
		//$tabs=['restaurant','cuisineCat'];
		if(!is_array($tabs)) return ;
		$current_tab=$row->post_type;
		$trad=str_replace(''.$row->post_type.'.show',$row->post_type,$view);
		//ddd($trad);
	@endphp
	<ul class="nav nav-pills mb-3 nav-justified" id="pills-tab" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" href="{{ $row->show }}">
				@lang($trad.'.tab.content')
			</a>
		</li>
		@foreach($tabs as $k=>$tab)
		@php
			switch($routename){
				case 'container0.show':
					$route=route('container0.container1.index',array_merge($params,['container1'=>$tab]));
				break;
				case 'container0.container1.show':
					$route=route('container0.container1.container2.index',array_merge($params,['container2'=>$tab]));
					$route=str_replace('cuisinecat','cuisineCat',$route);
				break;
				case 'container0.container1.container2.show':
					$route=route('container0.container1.container2.container3.index',array_merge($params,['container3'=>$tab]));
					$route=str_replace('cuisinecat','cuisineCat',$route);
				break;

				default:
					ddd($routename);
				break; 
			}
			
		@endphp 
		<li class="nav-item">
			<a class="nav-link {{ $tab==$current_tab?'active':'' }}" href="{{ $route }}" role="tab" aria-controls="pills-menu" aria-selected="{{ $tab==$current_tab?'true':'false' }}">@lang($trad.'.tab.'.$tab)</a> 
				{{-- [{{ $current_tab }}] --}}
		</li>
		@endforeach 
	</ul>
	<div class="tab-content" id="pills-tabContent">
		<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="pills-menu-tab">
      		<div class="container m-t-30">
				<div class="row">
					{{--
					@include('pub_theme::restaurant.show.tabs.'.$current_tab)
					@include('pub_theme::restaurant.'.$current_tab.'.tabs.show')
					--}}
				</div>
			</div>
		</div>
	</div>
</div>