<?php

namespace Msantang\QueryFilters;

trait Filterable
{
    /**
     * QueryFilter class.
     * @var string
     */
    protected $filter_class;

    /**
     * QueryFilterInput class.
     * @var string
     */
    protected $filter_input_class;

    /**
     * Creates local scope to run the filter.
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query)
    {
        // Resolve the current Model's filter
        $filter_class = $this->getFilterClass();
        $input_class = $this->getFilterInputClass();

        // create instances
        $filter = new $filter_class();
        $input = $input_class::fromRequest();

        // validate filter input or throw
        $input->validateOrFail();
        // apply filter
        $filter->apply($query, $input);
        // Return the filter query
        return $query;
    }

    /**
     * Filter with given input and filter instances.
     *
     * @param  $query
     * @param  FilterInput $input input definition
     * @param  Filter      $filter [description]
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterWith($query, $input, $filter)
    {
        // validate filter input or throw
        $input->validateOrFail();
        // apply filter
        $filter->apply($query, $input);

        return $query;
    }

    /**
     * Returns QueryFilter class to be instantiated.
     *
     * @return QueryFilter
     */
    protected function getFilterClass()
    {
        if (! $this->filter_class) {
            return config('queryfilters.namespace', 'App\\QueryFilters\\').class_basename($this).'Filter';
        }

        return $this->filter_class;
    }

    /**
     * Returns QueryFilterInput class to be instantiated.
     *
     * @return QueryFilter
     */
    protected function getFilterInputClass()
    {
        if (! $this->filter_input_class) {
            return config('queryfilters.namespace', 'App\\QueryFilters\\').class_basename($this).'FilterInput';
        }

        return $this->filter_input_class;
    }
}
