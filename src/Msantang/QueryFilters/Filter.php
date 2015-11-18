<?php
/**
 * @author Martin Alejandro Santangelo
 */

namespace Msantang\QueryFilters;


use Msantang\QueryFilters\Contracts\FilterInterface;
use Msantang\QueryFilters\Contracts\ParameterFilterInterface;

class Filter implements FilterInterface
{
    protected static $macros = [];

    protected $filters = [];

    public function __construct($filters = null)
    {
        if($filters) {
            $this->filters = $filters;
        }
    }

    public static function macro($name, $callback)
    {
        static::$macros[$name] = $callback;
    }

    public function apply($query, $input)
    {
        $this->explodeFilters();

        $data = $input->getMappedData();

        foreach ($data as $param => $value) {
            $this->applyParameterFilter($param, $value, $query);
        }
    }

    protected function applyParameterFilter($param, $value, $query)
    {
        foreach($this->filters[$param] as $k => $filters) {
            if (is_array($value)) {
                if (!array_key_exists($k, $value)) continue;
                $v = $value[$k];
            } else {
                $v = $value;
            }

            $class = ucfirst($filters[0]);
            $opt   = @$filters[1];

            $filter = $this->getParameterFilter($class);

            if ($filter instanceof ParameterFilterInterface) {
                $filter->apply($query, $v, $param, $opt);
            } else {
                if (is_callable($filter)) {
                    $filter($query, $v, $param, $opt);
                }
            }
        }
    }

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