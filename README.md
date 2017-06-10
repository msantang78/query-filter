# Laravel Query Filter #
[![Latest Stable Version](http://img.shields.io/packagist/v/msantang/query-filters.svg)](https://packagist.org/packages/msantang/query-filters) [![Total Downloads](http://img.shields.io/packagist/dt/msantang/query-filters.svg)](https://packagist.org/packages/msantang/query-filters)

With this package you can create filters for queries as well as validate the input values in a simple and clean way.

## Install ##

Laravel 5

```
        composer require msantang/query-filters 0.2.*
```


Laravel 4

```
        composer require msantang/query-filters 0.1.*
```

### Fast example ###
```
#!php

<?php
        // mapping from input to filter values
        $mapping = [
            'created_at' => 'created_from|created_to',  // multiple filters
            'id'         => 'id',
            'roles.name' => 'roles_name'   // filter a joined model (roles relation)
        ];

        // input validation
        $rules = [
            'created_from' => 'date',
            'created_to'   => 'date',
            'id'           => 'integer',
            'user.name'    => 'string'
        ];

        // make filter input from request
        $input = FilterInput::fromInput( $mapping, null, $rules);
        
        // Same as
        // $input = new FilterInput(request(), $mapping, null, $rules);

        // validate filter inputs
        if (!$input->validate()) {
            // do something with message bag
            dd($input->messages());
            return;
        };

        // construct filters
        $filter = new Filter([
            //field         field filters
            'created_at' => 'date:from|date:to',
            'id'         => 'numeric:eq',
            'roles.name' => 'string:contains'
        ]);
        
        $query = User::query();

        // apply filter to query
        $filter->apply($query, $input);

        return $query->get();
```
### Using Inheritance ###
```
#!php

<?php

        class UsersFilterInput extends Msantang\QueryFilters\FilterInput
        {
            // mapping from input to filter values
            protected $mapping = [
                'created_at' => 'created_from|created_to',  // multiple filters
                'id'         => 'id',
                'roles.name' => 'roles_name'   // filter a joined model (roles relation)
            ];

            // input validation
            protected $rules = [
                'created_from' => 'date',
                'created_to'   => 'date',
                'id'           => 'integer',
                'user.name'    => 'string'
            ];
        }

        class UsersFilter extends Msantang\QueryFilters\Filter
        {
            protected $filters = [
                'created_at' => 'date:from|date:to',
                'id'         => 'numeric:eq'
            ];
        }

        // make filter input from request
        $input = UsersFilterInput::fromInput();

        // construct filters
        $filter = new UsersFilter();

        // is valid?
        if (!$input->validate()) {
            // do something with message bag
            dd($input->messages());
        };

        $query = User::query();
        // apply filter to query
        $filter->apply($query, $input);

        return $query->get();
```

# TODO #
* clean and comment the code.
* TEST!!!