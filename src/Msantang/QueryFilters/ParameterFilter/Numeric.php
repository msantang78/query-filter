<?php

namespace Msantang\QueryFilters\ParameterFilter;

use Msantang\QueryFilters\Contracts\ParameterFilterInterface;

class Numeric implements ParameterFilterInterface
{
    public function apply($query, $value, $name,  $opt = null)
    {
        if (empty($opt)) $opt[0] = 'eq';

        switch ($opt[0]) {
            case 'gte':
                $query->where($name,'>=', $value);
                break;
            case 'lte':
                $query->where($name,'<=', $value);
                break;
            case 'gt':
                $query->where($name,'>', $value);
                break;
            case 'lt':
                $query->where($name,'<', $value);
                break;
            case 'neq':
                $query->where($name,'<>',$value);
                break;
            case 'eq':
            default:
                $query->where($name,$value);
                break;
        }
    }
}