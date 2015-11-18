<?php
namespace Msantang\QueryFilters\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Msantang\QueryFilters\FilterInput;

/**
 * @author Martin Alejandro Santangelo
 */
interface FilterInterface
{
    /**
     * Applies the filter
     *
     * @param Model|EloquentBuilder $query
     * @param FilterInput $input
     * @return EloquentBuilder
     */
    public function apply($query, $input);
}