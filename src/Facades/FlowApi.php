<?php

namespace DavidVegaCl\LaravelFlow\Facades;

use Illuminate\Support\Facades\Facade;

class FlowApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \DavidVegaCl\LaravelFlow\FlowApi::class;
    }
}
