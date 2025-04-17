@extends('adm_theme::layouts.app')
@section('page_heading','package extend')
@section('content')
@include('backend::includes.flash')
@include('backend::includes.components')

extend package ..

@endsection
