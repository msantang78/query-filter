<?php

namespace Msantang\QueryFilters;

use Validator;

class FilterInput
{
    protected $rules    = [];
    protected $mapping  = [];
    protected $defaults = [];
    protected $data     = [];

    protected $validator = null;

    public function validate()
    {
        $this->validator = Validator::make($this->data, $this->getRules());
        return !$this->validator->fails();
    }

    public function __construct($data, $mapping = null, $defaults = null, $rules = null)
    {
        $this->data = $data;

        if ($defaults) {
            $this->defaults = $defaults;
        }

        if ($rules) {
            $this->rules = $rules;
        }

        if($mapping) {
            $this->mapping = $mapping;
        }
    }

    /**
     * @return array|null
     */
    public function getRules()
    {
        return $this->rules;
    }


    public function getData()
    {
        return $this->data;
    }

    public function messages()
    {
        if (is_null($this->validator)) {
            $this->validate();
        }

        return $this->validator->messages();
    }

    public function getMappedData()
    {
        $d = [];
        foreach ($this->mapping as $field => $m) {
            $names = explode('|', $m);

            $tmp = [];

            foreach($names as $k => $name) {
                if (array_key_exists($name, $this->data)) {
                    $tmp[$k] = $this->data[$name];
                }
            }

            $d[$field] = $tmp;
        }

        return $d;
    }

    public static function fromInput($mapping = null, $defaults = null, $rules = null)
    {
        $request = app()['Illuminate\Http\Request'];
        return new static( $request->all(), $mapping, $defaults, $rules);
    }

    public static function fromArray($data,$mapping = null, $defaults = null, $rules = null)
    {
        return new static($data, $defaults, $rules);
    }
}