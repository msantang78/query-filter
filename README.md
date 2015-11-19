# Laravel Query Filter #

With this package you can make and validate query filters in a simple and clean way.

## Use ##

### Fast example ###
```
#!php

<?php
        // mapping from input to filter values
        $mapping = [
            'fecha'    => 'desde|hasta',
            'local_id' => 'local_id',
            'user_id'  => 'user_id'
        ];

        // input validation
        $rules = [
            'desde'    => 'date',
            'hasta'    => 'date',
            'local_id' => 'integer',
            'user_id'  => 'integer'
        ];

        // make filter input
        $input = FilterInput::fromInput( $mapping, null, $rules);

        // is valid?
        if (!$input->validate()) {
            // do something with message bag
            $input->messages()
        };

        // construct filters
        $f = new Filter([
            'fecha'    => 'date:from|date:to',
            'user_id'  => 'numeric:eq'
        ]);

        
        $query = Document::query();
        // apply filter to query
        $f->apply($query, $input);
```
### Using Inheritance ###
```
#!php

<?php

        class DocumentFilterInput extends Msantang\QueryFilters\FilterInput
        {
                // mapping from input to filter values
                $mapping = [
                    'fecha'    => 'desde|hasta',
                    'local_id' => 'local_id',
                    'user_id'  => 'user_id'
                ];

                // input validation
                $rules = [
                    'desde'    => 'date',
                    'hasta'    => 'date',
                    'local_id' => 'integer',
                    'user_id'  => 'integer'
                ];
        }

        class DocumentFilter extends Msantang\QueryFilters\Filter
        {
                protected $filters = [
                    'fecha'    => 'date:from|date:to',
                    'user_id'  => 'numeric:eq'
                ]
        }

        // make filter input
        $input = DocumentFilterInput::fromInput();

        // construct filters
        $f = new DocumentFilter();

        // is valid?
        if (!$input->validate()) {
            // do something with message bag
            $input->messages()
        };

        $query = Document::query();
        // apply filter to query
        $f->apply($query, $input);
```

# TODO #
* clean and comment the code.
* TEST!!!