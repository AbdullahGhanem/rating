<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rating Bounds
    |--------------------------------------------------------------------------
    |
    | Set the minimum and maximum allowed rating values. Ratings outside
    | this range will throw a ValidationException.
    | Set to null to disable validation.
    |
    */

    'min' => null,
    'max' => null,

    /*
    |--------------------------------------------------------------------------
    | Allow Negative Ratings
    |--------------------------------------------------------------------------
    |
    | When set to false, any rating below 0 will be rejected.
    | This is independent of the min/max bounds above.
    |
    */

    'allow_negative' => true,

];
