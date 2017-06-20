<?php

namespace Msantang\QueryFilters;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Input parser and validator for filters.
 */
class FilterInput
{
    /**
     * Validation rules.
     * @var array
     */
    protected $rules = [];
    /**
     * Input mapping.
     * @var array
     */
    protected $mapping = [];
    /**
     * Default values.
     * @var array
     */
    protected $defaults = [];
    /**
     * Input data.
     * @var array
     */
    protected $data = [];

    /**
     * Validator instance.
     * @var Validator
     */
    protected $validator = null;

    /**
     * Validate input.
     * @return [bool]
     */
    public function validate()
    {
        if (empty($this->rules)) {
            return true;
        }

        return ! $this->getValidator()->fails();
    }

    /**
     * Validate or throw.
     * @throws ValidationException
     */
    public function validateOrFail()
    {
        if (empty($this->rules)) {
            return true;
        }

        $validator = $this->getValidator();

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        $this->validator = Validator::make($this->data, $this->getRules());

        return $this->validator;
    }

    /**
     * Constructor.
     * @param array $data     Input data
     * @param array $mapping  Mapping inputs to filters
     * @param array $defaults Deaults (null for no defaults)
     * @param array $rules    Validation rules
     */
    public function __construct($data, $mapping = null, $defaults = null, $rules = null)
    {
        $this->data = $data;

        if ($defaults) {
            $this->defaults = $defaults;
        }

        if ($rules) {
            $this->rules = $rules;
        }

        if ($mapping) {
            $this->mapping = $mapping;
        } else {
            $this->mapping = $this->getDefaultMapping();
        }
    }

    protected function getDefaultMapping()
    {
        $keys = array_keys($this->data);
        // filter operator parameters
        $keys = array_filter($keys, function ($v) {
            return ! ends_with($v, '_opt');
        });

        return array_combine($keys, $keys);
    }

    /**
     * @return array|null
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get Validation messages.
     * @return MessageBag
     */
    public function messages()
    {
        if (is_null($this->validator)) {
            $this->validate();
        }

        return $this->validator->messages();
    }

    /**
     * Map Input data.
     * @return array
     */
    public function getMappedData()
    {
        $mapped_data = [];
        foreach ($this->mapping as $field => $m) {
            $names = explode('|', $m);

            $tmp = [];

            foreach ($names as $k => $name) {
                if (array_key_exists($name, $this->data)) {
                    $tmp[$k][] = $this->data[$name];

                    if (array_key_exists($name.'_opt', $this->data)) {
                        $tmp[$k][] = $this->data[$name.'_opt'];
                    }
                }
            }

            $mapped_data[$field] = $tmp;
        }

        return $mapped_data;
    }

    /**
     * Create an instance of input with data from Http Request.
     *
     * @param array $data     Input data
     * @param array $mapping  Mapping inputs to filters
     * @param array $defaults Deaults (null for no defaults)
     * @param array $rules    Validation rules
     */
    public static function fromInput($mapping = null, $defaults = null, $rules = null)
    {
        self::fromRequest($mapping, $defaults, $rules);
    }

    /**
     * Create an instance of input with data from Http Request.
     *
     * @param array $data     Input data
     * @param array $mapping  Mapping inputs to filters
     * @param array $defaults Deaults (null for no defaults)
     * @param array $rules    Validation rules
     */
    public static function fromRequest($mapping = null, $defaults = null, $rules = null)
    {
        $request = request();

        return new static($request->all(), $mapping, $defaults, $rules);
    }

    /**
     * Create an instance of input with data from an array.
     *
     * @param array $data     Input data
     * @param array $mapping  Mapping inputs to filters
     * @param array $defaults Deaults (null for no defaults)
     * @param array $rules    Validation rules
     */
    public static function fromArray($data, $mapping = null, $defaults = null, $rules = null)
    {
        return new static($data, $mapping, $defaults, $rules);
    }
}
