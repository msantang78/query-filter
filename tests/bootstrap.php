<?php

require 'vendor/autoload.php';
use Mockery as m;

function config($path, $default)
{
    return $default;
}

function request()
{
    $request = m::mock('Illuminate\Http\Request');
    $request->shouldReceive('all')
            ->once()
            ->andReturn([]);

    return $request;
}

function app($class)
{
    return new $class;
}
