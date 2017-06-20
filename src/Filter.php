<?php
/**
 * @author Martin Alejandro Santangelo
 */

namespace Msantang\QueryFilters;

use Msantang\QueryFilters\Contracts\FilterInterface;

/**
 * Class Filter.
 */
class Filter implements FilterInterface
{
    /**
     * @var array
     */
    protected static $macros = [];
    /**
     * @var array|null
     */
    protected $filters = [];

    /**
     * @param null $filters
     */
    public function __construct($filters = null)
    {
        if ($filters) {
            $this->filters = $filters;
        }
    }

    /**
     * Add a new filter.
     *
     * @param $name
     * @param $callback
     */
    public static function macro($name, $callback)
    {
        static::$macros[$name] = $callback;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|Contracts\Model $query
     * @param FilterInput $input
     */
    public function apply($query, $input)
    {
        $this->explodeFilters();

        $data = $input->getMappedData();

        foreach ($data as $param => $value) {
            $this->applyParameterFilter($param, $value, $query);
        }
    }

    protected function getFilter($filter_definition)
    {
        $opt = null;

        if (is_array($filter_definition)) {
            $type = ucfirst($filter_definition[0]);
            $opt = @$filter_definition[1];

            $filter = $this->getParameterFilter($type);
        } else {
            $filter = $filter_definition;
        }

        if (! is_callable($filter)) {
            throw new Exception("$param: ParameterFilter is not callable");
        }
        return [$filter, $opt];
    }

    /**
     * Apply the filters to a parameter.
     *
     * @param $param
     * @param $value
     * @param $query
     */
    protected function applyParameterFilter($param, $values, $query)
    {
        foreach ($this->filters[$param] as $k => $filter_definition) {
            // if there is no value skip it
            if (! array_key_exists($k, $values)) {
                continue;
            }

            $fvalue = $values[$k][0];
            // get the filter by definition
            list($filter, $opt) = $this->getFilter($filter_definition);

            // if options come
            if (count($values[$k]) > 1) {
                array_shift($values[$k]);
                $opt = $values[$k];
            }
            // cheque if it's a joined filter
            $fparam = $this->joined($param);

            // is joined?
            if (is_array($fparam)) {
                $this->applyJoinedFilter($query, $fparam, $filter, $fvalue, $opt);
            } else {
                $filter($query, $fvalue, $fparam, $opt);
            }
        }
    }

    /**
     * Apply a filter to joined columns.
     * @param  $query
     * @param  array $joins  An array of join relations
     * @param  callable $filter Filter
     * @param  $value
     */
    protected function applyJoinedFilter($query, $joins, $filter, $value, $opt)
    {
        $param = array_pop($joins);

        $f = function ($q) use ($filter, $param, $value, $opt) {
            $filter($q, $value, $param, $opt);
        };

        while (count($joins) > 1) {
            $rel = array_pop($joins);

            $f = function ($q) use ($rel, $f, $filter, $param, $value, $opt) {
                $q->whereHas($rel, $f);
            };
        }

        $rel = array_pop($joins);

        $query->whereHas($rel, $f);
    }

    /**
     * Return an string if $param is a column on the currento model or array if it is joined.
     *
     * @param $param
     * @return array|string
     */
    protected function joined($param)
    {
        $r = explode('.', $param);

        if (count($r) == 1) {
            return $param;
        }

        return $r;
    }

    /**
     * @param string $type
     * @return mixed
     */
    protected function getParameterFilter($type)
    {
        if (array_key_exists($type, static::$macros)) {
            return static::$macros[$type];
        }

        return app('\\Msantang\\QueryFilters\\ParameterFilter\\'.$type.'Filter');
    }

    /**
     * Process filter definition string and return an array.
     *
     * @return array
     */
    private function explodeFilters()
    {
        foreach ($this->filters as &$f) {
            if (is_string($f)) {
                $f = explode('|', $f);

                foreach ($f as &$ff) {
                    $ff = explode(':', $ff);

                    if (count($ff) == 2) {
                        $ff[1] = explode(',', $ff[1]);
                    }
                }
            }
        }
    }
}
