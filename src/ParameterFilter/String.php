<?php

namespace Msantang\QueryFilters\ParameterFilter;

use Msantang\QueryFilters\Contracts\ParameterFilterInterface;

class String extends AbstractParameterFilter implements ParameterFilterInterface
{
    public function apply($query, $value, $name,  $opt = null)
    {
        if (empty($opt)) $opt[0] = 'contains';

        switch ($opt[0]) {
            case 'start':
                $query->where($name,'like', "$value%");
                break;
            case 'end':
                $query->where($name,'like', "%$value");
                break;
            case 'equal':
                $query->where($name, $value);
                break;
            case 'words':
                $filters = explode(' ', $value);
                foreach ($filters as $e)
                {
                    $query->whereRaw("descripcion REGEXP ?",['(^| )(^.*)'.$e.'.*(    |$)']);
                }
                break;
            case 'contains':
            default:
                $query->where($name,'like', "%$value%");
                break;
        }

    }
}