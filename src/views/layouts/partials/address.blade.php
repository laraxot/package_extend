@php
	if(!is_object($row)) return;
@endphp
<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
	<i class="fa fa-map-marker" aria-hidden="true"></i>
		<span itemprop="postalCode">{{ $row->postal_code }}</span>
		<span itemprop="addressLocality">{{ $row->locality }}</span>,
		(<span itemprop="addressRegion">{{ $row->administrative_area_level_2_short }}</span>)
		<meta itemprop="addressCountry" content="{{ $row->country_short}}" /> 
</div>