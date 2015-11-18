# Laravel Query Filter #

With this package you can make and validate filters for your querys in a simpler and cleaner way

## Use ##


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


TODO: clean and comment the code. 

