# Laravel Eloquent/Query Filter #
[![Latest Stable Version](http://img.shields.io/packagist/v/msantang/query-filters.svg)](https://packagist.org/packages/msantang/query-filters) 
[![Total Downloads](http://img.shields.io/packagist/dt/msantang/query-filters.svg)](https://packagist.org/packages/msantang/query-filters)
[![License](https://img.shields.io/packagist/l/msantang/query-filters.svg)](https://packagist.org/packages/msantang/query-filters)

Filter Eloquent models in a simple and clean way. 

You can filter model with url call´s like this
```
  /users/?created_from=2017-01-01&created_to=2017-01-01&roles_name=Admin
  /users/?id=2
  /users/?id[]=1&id[]=3&id[]=6
```
Just with
```
#!php
<?php
Route::get('user/', function () {
    return App\User::filter()->get();
});
```
## Features ##
* Input values validation
* Support multiple filters by fields
* Filter remote fields through relations
* Custom filter types through classes or closures

[Install](#install)
[Usage ](#usage)

## Install

Require with composer

```
composer require msantang/query-filters 0.2.*
```
Add service provider to config/app.php (If you want to use creator command)

```
Msantang\QueryFilters\QueryFiltersServiceProvider::class,
```

## Usage ##


### Create Filter and InputFilter ###

```
php artisan queryfilter:make User
```
By default this will create two files

App/QueryFilters/UserFilter.php:

```
#!php
<?php namespace App\QueryFilters;

use Msantang\QueryFilters\Filter;

class UserFilter extends Filter
{
    protected $filters = [
     // field            type:operation
        'created_at'  => 'date:from|date:to',  // multiple filter
        'name'        => 'string:begin',
        'roles.name'  => 'string:contains',    // filter on joined model (Roles)
        'id'          => 'numeric:eq'
    ];
}
```
and App/QueryFilters/UserFilterInput.php:
```
#!php
<?php namespace App\QueryFilters;

use Msantang\QueryFilters\FilterInput;

class UserFilterInput extends FilterInput
{
    // mapping from input to filter values
    protected $mapping = [
     // field           input name
        'id'         => 'id',
        'created_at' => 'desde|hasta',  // multiple filters
        'name'       => 'name',
        'roles.name' => 'roles_name'   // filter a joined model (user relation)
    ];

    // input validation
    protected $rules = [
        'id'         => 'integer',
        'desde'      => 'date',
        'hasta'      => 'date',
        'name'       => 'string',
        'roles.name' => 'string'
    ];
}
```

### Add Filterable to your model ###
```
#!php
<?php

namespace App;

use Msantang\QueryFilters\Filterable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Filterable;
}
```

### Using constructors ###
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
    'roles.name'   => 'string'
];

// make filter input from request
$input = FilterInput::fromRequest( $mapping, null, $rules );

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

Create Filter Input Class
```
#!php
<?php 
namespace App\Filters;

use Msantang\QueryFilters\FilterInput;

class UsersFilterInput extends FilterInput
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
```
Create Filter Class
```
#!php
<?php
namespace App\Filters;

use Msantang\QueryFilters\Filter;

class UsersFilter extends Filter
{
    protected $filters = [
        //field         field filters
        'created_at' => 'date:from|date:to',
        'id'         => 'numeric:eq'
    ];
}
```
Use filter
```
#!php
<?php
use App\Filters\UsersFilterInput;
use App\Filters\UserFilter;
// make filter input from request
$input = UsersFilterInput::fromRequest();
// could build it from array too: UsersFilterInput::fromArray(['id'=>2])

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
## Filter Types##

### Numeric ###
| Name          |   Operation  |      Accept   |
|---------------|:-------------|--------------:|
| numeric:eq    | Equal        | numeric,array |
| numeric:neq   | Not Eequal   | numeric,array |
| numeric:gt    | Grater       | numeric       |
| numeric:lt    | Less         | numeric       |
| numeric:gte   | Grater or Eq | numeric       |
| numeric:lte   | Less or Eq   | numeric       |

### String ###
| Name            |   Operation                        | Accept        |
|-----------------|:-----------------------------------|--------------:|
| string:eq       | Equal                              | string        |
| string:begin    | Begin with                         | string        |
| string:end      | End with                           | string        |
| string:contains | End with                           | string        |
| string:words    | Search every word (space separated)| string        |

### Date ###
| Name          |   Operation  |Accept|
|---------------|:-------------|-----:|
| date:eq       | Equal        | date |
| date:neq      | Not Eequal   | date |
| date:from     | Grater or Eq | date |
| date:to       | Less or Eq   | date |
| date:after    | Grater than  | date |
| date:before   | Less than    | date |

### Datetime ###
| Name              |   Operation  | Accept   |
|-------------------|:-------------|---------:|
| datetime:eq       | Equal        | datetime |
| datetime:neq      | Not Eequal   | datetime |
| datetime:from     | Grater or Eq | datetime |
| datetime:to       | Less or Eq   | datetime |
| datetime:after    | Grater than  | datetime |
| datetime:before   | Less than    | datetime |

## Creating custom fields filters ##

Despite the build in field filters, you can add your own filters.

### Closures ###
```
#!php
<?php
$filters = [
   'created_at' => 'date:from|date:to',
   'id'         => [function($query, $value, $name,  $opt ){
         $query->where($name, $value);
    }]
];

```

### Classes ###

```
#!php
<?php
use Msantang\QueryFilters\Contracts\ParameterFilterInterface;
use Msantang\QueryFilters\ParameterFilter\AbstractParameterFilter;

class MyFilter extends AbstractParameterFilter implements ParameterFilterInterface
{
    public function apply($query, $value, $name,  $opt = null)
    {
        if (empty($opt)) $opt[0] = 'eq';

        switch ($opt[0]) {
            case 'eq':
                $query->where($name,'=', $value);
                break;
            
            default:
                $query->where($name,'like', "%$value%");
                break;
        }

    }
}
```

```
#!php
<?php
$f = new Filter([
    'created_at'  => 'date:from|date:to',
    'name'        => 'string:begin',
    'roles.name'  => 'string:contains',
    'id'          => [new MyFilter]
]);
```

## Changing the operation for a field filter ##

You could have a filter defined like this:

```
#!php
<?php
$mapping = [
    'id' => 'byid',
];

$filter = [
    'id' => 'numeric:eq'
]
```
and then select an other operation dinamicaly

```
  /users/?byid=2&byid_opt=neq
```
{mapping_name}_opt = operation

# TODO #
* TEST!!!