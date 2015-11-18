<?php

namespace Msantang\QueryFilters\ParameterFilter;

use Msantang\QueryFilters\Contracts\ParameterFilterInterface;
use Carbon\Carbon;

class Date implements ParameterFilterInterface
{
    public function apply($query, $value, $name,  $opt = null)
    {
        //if (is_null($value)) return;

        $value = new Carbon($value);

        $value = $value->format('Y-m-d');

        if (empty($opt)) $opt[0] = 'eq';

        switch ($opt[0]) {
            case 'to':
                $query->where($name,'<=', $value);
                break;
            case 'from':
                $query->where($name,'>=', $value);
                break;
            case 'after':
                $query->where($name,'>', $value);
                break;
            case 'before':
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