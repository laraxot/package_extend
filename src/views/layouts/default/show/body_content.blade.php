@php
	$item_view=$view.'.item.'.$show_type;
	$item_view_default=$view_default.'.item.'.$show_type;
	if(!\View::exists($item_view) && !\View::exists($item_view_default)){
		ddd('not exist ['.$item_view.'] ['.$item_view_default.']');
	}
@endphp
@includeFirst([$item_view,$item_view_default])
