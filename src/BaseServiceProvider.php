<?php
namespace XRA\Extend;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use XRA\Extend\Traits\ServiceProviderTrait;


abstract class BaseServiceProvider extends ServiceProvider implements DeferrableProvider
{
	use ServiceProviderTrait;

    protected $defer = true;
}