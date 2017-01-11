<?php

return [

    //FQN of the Product model
    'product_model' =>  'App\Models\Product',

    /*
    |--------------------------------------------------------------------------
    | Default tax rate
    |--------------------------------------------------------------------------
    |
    | This default tax rate will be used when you make a class implement the
    | Taxable interface and use the HasTax trait.
    |
    */
    'cookie_name'   =>  '_laracart',
    'lifetime'   =>  2628000,  //minutes default is 5 years

    'tax_rate' => 0,
    /*
    |--------------------------------------------------------------------------
    | Default number format
    |--------------------------------------------------------------------------
    |
    | This defaults will be used for the formated numbers if you don't
    | set them in the method call.
    |
    */
    'format' => [
        'decimals' => 2,
        'decimal_point' => '.',
        'thousand_seperator' => ','
    ],

];