<?php
namespace Msantang\QueryFilters\ParameterFilter;


class AbstractParameterFilter
{
    public function __invoke($query, $value, $name,  $opt = null)
    {
        $this->apply($query, $value, $name,  $opt);
    }
}