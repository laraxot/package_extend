@php
	//ddd($show_type);
	$item_view=$view.'.item.'.$show_type;
	$item_view_default=$view_default.'.item.'.$show_type;
	$item_view_extend=$view_extend.'.item.'.$show_type;
	if(!\View::exists($item_view) && !\View::exists($item_view_default) &&!\View::exists($item_view_extend) ){
		ddd('not exist ['.$item_view.'] ['.$item_view_default.']['.$item_view_extend.']');
	}
@endphp
@includeFirst([$item_view,$item_view_default,$item_view_extend])
