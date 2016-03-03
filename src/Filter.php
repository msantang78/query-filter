<?php
/**
 * @author Martin Alejandro Santangelo
 */

namespace Msantang\QueryFilters;


use Msantang\QueryFilters\Contracts\FilterInterface;

/**
 * Class Filter
 * @package Msantang\QueryFilters
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
        if($filters) {
            $this->filters = $filters;
        }
    }

    /**
     * Add a new filter
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

    /**
     * Apply the filters to a parameter
     *
     * @param $param
     * @param $value
     * @param $query
     */
    protected function applyParameterFilter($param, $value, $query)
    {
        foreach($this->filters[$param] as $k => $filters) {

            // if there is no value skip it
            if (!array_key_exists($k, $value)) continue;

            $v = $value[$k];

            $class = ucfirst($filters[0]);
            $opt   = @$filters[1];

            $filter = $this->getParameterFilter($class);

            if (!is_callable($filter)) throw new \Exception('ParameterFilter is not callable');

            $p = $this->joined($param);

            // is joined?
            if (is_array($p)) {

                $param = array_pop($p);

                $f = function($q) use($filter, $param, $v, $opt) {
                    $filter($q, $v, $param, $opt);
                };

                while (count($p) > 1) {
                    $rel = array_pop($p);

                    $f = function($q) use($rel, $f, $filter, $param, $v, $opt) {
                        $q->whereHas($rel, $f);
                    };
                }

                $rel = array_pop($p);

                $query->whereHas($rel, $f);

            } else {
                $filter($query, $v, $param, $opt);
            }
        }
    }

    /**
     * @param $param
     * @return array|string
     */
    protected function joined($param)
    {
        $r = explode('.', $param);

        if (count($r) == 1) return $param;

        return $r;
    }

    /**
     * @param string $class
     * @return mixed
     */
    protected function getParameterFilter($class)
    {
        if (array_key_exists($class, static::$macros)) {
            return static::$macros[$class];
        }

        return app('\\Msantang\\QueryFilters\\ParameterFilter\\'.$class);
    }

    private function explodeFilters()
    {
        foreach($this->filters as &$f) {

            if (is_string($f)) {
                $f =  explode('|',$f);

                foreach($f as &$ff) {
                    $ff = explode(':',$ff);

                    if(count($ff) == 2) {
                        $ff[1] = explode(',',$ff[1]);
                    }
                }
            }
        }
    }
}