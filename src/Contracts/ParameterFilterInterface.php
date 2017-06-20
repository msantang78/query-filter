<?php

namespace Msantang\QueryFilters\Contracts;

interface ParameterFilterInterface
{
    public function apply($query, $value, $name, $opt = null);
}
