
<div class="breadcrumb">
	<div class="container">
		<ul itemscope itemtype="http://schema.org/BreadcrumbList">
			<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a itemscope itemtype="http://schema.org/Thing" itemprop="item" href="{{ asset(App::getLocale()) }}">
					<span itemprop="name">Home</span>
				</a>
				 <meta itemprop="position" content="1" />
			</li>
			{{--
			@foreach($row->breadcrumbs() as $k=>$bread)
			<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a itemscope itemtype="http://schema.org/Thing" itemprop="item" href="{{ $bread->url }}">
					<span itemprop="name">{{ $bread->title }}</span>
				</a>
				 <meta itemprop="position" content="{{ $k }}" />
			</li>
			@endforeach
			--}}
			{{--
			<li>{{ $row->title }}</li>
			--}}
			{{--
			<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a itemscope itemtype="http://schema.org/Thing" itemprop="item" href="{{ $row->url }}">
					<span itemprop="name">{{ $row->title }}</span>
				</a>
				<meta itemprop="position" content="4" />
			</li>
			--}}
		</ul>
	</div>
</div>
